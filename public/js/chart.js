// assets/js/dashboard-charts.js
import { Chart } from 'chart.js';

export function initDashboardCharts(userStats, reclamationStats, projetStats) {
    // User Roles Chart
    const userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
    new Chart(userRolesCtx, {
        type: 'bar',
        data: {
            labels: userStats.map(item => item.role),
            datasets: [{
                label: 'Utilisateurs',
                data: userStats.map(item => item.count),
                backgroundColor: '#4361ee',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // User Registrations Chart
    const userRegCtx = document.getElementById('userRegistrationsChart').getContext('2d');
    new Chart(userRegCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: [{
                label: 'Inscriptions',
                data: [12, 19, 3, 5, 2, 3, 7, 10, 15, 8, 4, 6],
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Reclamation Status Chart
    const reclamationCtx = document.getElementById('reclamationStatusChart').getContext('2d');
    new Chart(reclamationCtx, {
        type: 'doughnut',
        data: {
            labels: reclamationStats.map(item => item.statut),
            datasets: [{
                data: reclamationStats.map(item => item.count),
                backgroundColor: ['#4361ee', '#3a0ca3', '#7209b7']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Project Status Chart
    const projectCtx = document.getElementById('projectStatusChart').getContext('2d');
    new Chart(projectCtx, {
        type: 'pie',
        data: {
            labels: projetStats.map(item => item.status),
            datasets: [{
                data: projetStats.map(item => item.count),
                backgroundColor: ['#4361ee', '#3a0ca3', '#7209b7']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}