<?php
session_name('SCHOOL_SECURE_SESSION');
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}


try {
    $role = $_SESSION['role'];
    $teacher_id = $_SESSION['teacher_id'] ?? 0;
    $student_id = $_SESSION['student_id'] ?? 0;
    $class_id = $_SESSION['class_id'] ?? 0;

    if ($role === 'admin') {
        $actualTeacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
        $actualStudentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        $actualSubjectCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
        $actualClassroomCount = $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn();

        $gradeStmt = $pdo->query("SELECT grade, COUNT(*) as count FROM grades WHERE grade IS NOT NULL GROUP BY grade");
        $gradeRows = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

        $attDatesStmt = $pdo->query("SELECT DISTINCT attendance_date FROM attendance ORDER BY attendance_date DESC LIMIT 5");
    } elseif ($role === 'teacher') {
        $actualTeacherCount = 1; // Only self
        // Students in their classes
        $stmtS = $pdo->prepare("SELECT COUNT(DISTINCT st.id) FROM students st 
                               JOIN schedules sch ON st.class_id = sch.class_id 
                               WHERE sch.teacher_id = ?");
        $stmtS->execute([$teacher_id]);
        $actualStudentCount = $stmtS->fetchColumn();

        $stmtSub = $pdo->prepare("SELECT COUNT(DISTINCT subject_id) FROM schedules WHERE teacher_id = ?");
        $stmtSub->execute([$teacher_id]);
        $actualSubjectCount = $stmtSub->fetchColumn();
        
        $actualClassroomCount = $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn(); // Still show total for context or filter to used rooms? Total is fine.

        $gradeStmt = $pdo->prepare("SELECT grade, COUNT(*) as count FROM grades g 
                                    JOIN schedules sch ON g.subject_id = sch.subject_id 
                                    WHERE sch.teacher_id = ? AND g.grade IS NOT NULL GROUP BY g.grade");
        $gradeStmt->execute([$teacher_id]);
        $gradeRows = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

        $attDatesStmt = $pdo->prepare("SELECT DISTINCT a.attendance_date FROM attendance a 
                                       JOIN schedules sch ON a.class_id = sch.class_id 
                                       WHERE sch.teacher_id = ? ORDER BY a.attendance_date DESC LIMIT 5");
        $attDatesStmt->execute([$teacher_id]);
    } else {
        // Student
        $actualTeacherCount = $pdo->prepare("SELECT COUNT(DISTINCT teacher_id) FROM schedules WHERE class_id = ?");
        $actualTeacherCount->execute([$class_id]);
        $actualTeacherCount = $actualTeacherCount->fetchColumn();

        $actualStudentCount = $pdo->prepare("SELECT COUNT(*) FROM students WHERE class_id = ?");
        $actualStudentCount->execute([$class_id]);
        $actualStudentCount = $actualStudentCount->fetchColumn();

        $actualSubjectCount = $pdo->prepare("SELECT COUNT(DISTINCT subject_id) FROM schedules WHERE class_id = ?");
        $actualSubjectCount->execute([$class_id]);
        $actualSubjectCount = $actualSubjectCount->fetchColumn();

        $actualClassroomCount = 0; // Not relevant

        // Distribution isn't relevant for single student, maybe show their own grades? 
        // We'll leave it as 0 or empty for now or hide on frontend.
        $gradeRows = [];
        $attDatesStmt = $pdo->prepare("SELECT DISTINCT attendance_date FROM attendance WHERE student_id = ? ORDER BY attendance_date DESC LIMIT 5");
        $attDatesStmt->execute([$student_id]);
    }
    
    // Initialize grade counters
    $gradeData = [
        '0' => 0, '1' => 0, '1.5' => 0, '2' => 0, '2.5' => 0, '3' => 0, '3.5' => 0, '4' => 0
    ];
    $totalGrades = 0;
    foreach ($gradeRows as $row) {
        $g = (string)$row['grade'];
        if (isset($gradeData[$g])) {
            $gradeData[$g] = (int)$row['count'];
            $totalGrades += (int)$row['count'];
        }
    }
    // We can group them simply: 0, 1-1.5, 2-2.5, 3-3.5, 4. Or just return the array.
    $gradeChartLabels = ['เกรด 0', 'เกรด 1-1.5', 'เกรด 2-2.5', 'เกรด 3-3.5', 'เกรด 4'];
    $gradeChartData = [
        $gradeData['0'],
        $gradeData['1'] + $gradeData['1.5'],
        $gradeData['2'] + $gradeData['2.5'],
        $gradeData['3'] + $gradeData['3.5'],
        $gradeData['4']
    ];

    // Attendance Chart Data (Last 5 distinct dates)
    // For simplicity, we get the last 5 days where attendance was taken
    $recentDates = $attDatesStmt->fetchAll(PDO::FETCH_COLUMN);
    $recentDates = array_reverse($recentDates); // oldest first
    
    $attLabels = [];
    $attData = [];

    foreach($recentDates as $date) {
        if ($role === 'student') {
            $stmt = $pdo->prepare("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN `status` = 'present' THEN 1 ELSE 0 END) as present_count
                FROM attendance WHERE attendance_date = ? AND student_id = ?");
            $stmt->execute([$date, $student_id]);
        } elseif ($role === 'teacher') {
             $stmt = $pdo->prepare("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN `status` = 'present' THEN 1 ELSE 0 END) as present_count
                FROM attendance a
                JOIN schedules sch ON a.class_id = sch.class_id
                WHERE a.attendance_date = ? AND sch.teacher_id = ?");
             $stmt->execute([$date, $teacher_id]);
        } else {
            $stmt = $pdo->prepare("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN `status` = 'present' THEN 1 ELSE 0 END) as present_count
                FROM attendance WHERE attendance_date = ?");
            $stmt->execute([$date]);
        }
        $res = $stmt->fetch();
        $total = $res['total'];
        $present = $res['present_count'];
        
        $percentage = ($total > 0) ? round(($present / $total) * 100) : 0;
        
        // Format date to short Thai format (e.g. 15 ต.ค.)
        $timestamp = strtotime($date);
        $thaiMonths = ["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
        $formattedDate = date('j', $timestamp) . ' ' . $thaiMonths[(int)date('n', $timestamp)];

        $attLabels[] = $formattedDate;
        $attData[] = $percentage;
    }

    if (empty($attLabels)) {
        // Fallback dummy
        $attLabels = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        $attData = [0,0,0,0,0];
    }

} catch (PDOException $e) {
    $actualTeacherCount = $actualStudentCount = $actualSubjectCount = $actualClassroomCount = 0;
    $gradeChartLabels = ['เกรด 0', 'เกรด 1-1.5', 'เกรด 2-2.5', 'เกรด 3-3.5', 'เกรด 4'];
    $gradeChartData = [0,0,0,0,0];
    $attLabels = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
    $attData = [0,0,0,0,0];
}

$chart_data = [
    'grades' => [
        'labels' => $gradeChartLabels,
        'data' => $gradeChartData
    ],
    'attendance' => [
        'labels' => $attLabels,
        'data' => $attData
    ]
];

$data = [
    'teacher_count' => $actualTeacherCount,
    'student_count' => $actualStudentCount,
    'subject_count' => $actualSubjectCount,
    'classroom_count' => $actualClassroomCount,
    'charts' => $chart_data
];

echo json_encode(['status' => 'success', 'data' => $data]);
?>
