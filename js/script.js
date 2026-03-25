$(document).ready(function() {
    // Toggle Sidebar Desktop & Mobile
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });

    // Close Sidebar on Mobile via close button
    $('#sidebarToggleClose').on('click', function() {
        $('#sidebar').removeClass('active');
    });

    // Ensure chart responsive resize on sidebar toggle
    $('#sidebarToggle').on('click', function() {
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 300);
    });

    // Handle Submenu active states
    $('.components > li > a').on('click', function() {
        $('.components > li').removeClass('active');
        if(!$(this).attr('data-bs-toggle')) {
            $(this).parent().addClass('active');
        } else {
            $(this).parent().addClass('active');
        }
    });

    // Initialize Chart.js
    initCharts();
});

window.attendanceChartInstance = null;
window.gradeChartInstance = null;

function initCharts() {
    // Attendance Chart (Line Chart)
    const attendanceCanvas = document.getElementById('attendanceChart');
    if(attendanceCanvas) {
        const ctx1 = attendanceCanvas.getContext('2d');
        window.attendanceChartInstance = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['-', '-', '-', '-', '-'],
                datasets: [{
                    label: 'นักเรียนมาเรียน (%)',
                    data: [0, 0, 0, 0, 0],
                    borderColor: '#355872',
                    backgroundColor: 'rgba(53, 88, 114, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#355872',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    title: { 
                        display: true, 
                        text: 'แนวโน้มการมาเรียนในรอบ 5 วันล่าสุด',
                        font: { size: 16, family: 'Sarabun' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    }

    // Grade Distribution Chart (Bar Chart)
    const gradeCanvas = document.getElementById('gradeChart');
    if(gradeCanvas) {
        const ctx2 = gradeCanvas.getContext('2d');
        window.gradeChartInstance = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['เกรด 0', 'เกรด 1-1.5', 'เกรด 2-2.5', 'เกรด 3-3.5', 'เกรด 4'],
                datasets: [{
                    label: 'จำนวนนักเรียน',
                    data: [0, 0, 0, 0, 0],
                    backgroundColor: [
                        '#dc3545', // Danger
                        '#fd7e14', // Warning-ish
                        '#ffc107', // Warning
                        '#20c997', // Teal
                        '#355872'  // Primary
                    ],
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: { 
                        display: true, 
                        text: 'ภาพรวมผลการเรียน (Grade Distribution)',
                        font: { size: 16, family: 'Sarabun' }
                    }
                }
            }
        });
    }
}
