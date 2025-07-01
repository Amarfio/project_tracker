angular.module('sheetApp')
    .controller('UserDetailsCtrl', ['$scope', '$http', '$location', function($scope, $http, $location) {
        // Initialize scope variables
        $scope.user_info = {
            username: 'Guest',
            role: 'developer',
            gender: 'Male',
            dept_id: 1
        };
        $scope.profile_pic_true = null;
        $scope.profile_pic = null;
        $scope.user = { name: 'Unknown' };
        $scope.user_id = $location.search().id || '';
        $scope.userName = $location.search().name || '';

        // Function to format date as YYYY-MM-DD
        function formatDate(dateString) {
            if (!dateString) return null;
            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Get date range from localStorage and format properly
        const fromDate = localStorage.getItem('fromDate');
        const toDate = localStorage.getItem('toDate');

        $scope.fromDate = fromDate ? formatDate(fromDate) : formatDate(new Date(new Date().getFullYear(), 0, 1));
        $scope.toDate = toDate ? formatDate(toDate) : formatDate(new Date());

        // Initialize metrics with default values
        $scope.metrics = {
            taskCompletionEfficiency: 0,
            efficiencyStatus: { text: 'No Data', class: 'neutral' },
            avgCompletionTime: 0,
            projectsCompleted: 0,
            completedTasks: 0,
            totalTasks: 0,
            completionRate: 0,
            productivityRate: 0,
            overallGrowth: 0,
            totalProjects: 0
        };

        // Function to format hours into days and hours
        $scope.formatTime = function(hours) {
            if (hours === 0) return '0 hours';
            const days = Math.floor(hours / 24);
            const remainingHours = (hours % 24).toFixed(2);
            if (days === 0) return `${remainingHours} hours`;
            return `${days} day${days !== 1 ? 's' : ''} ${remainingHours} hour${remainingHours !== 1 ? 's' : ''}`;
        };

        // Function to determine efficiency status and class
        $scope.getEfficiencyStatus = function(efficiency) {
            if (efficiency < 25) {
                return { text: 'Consistently Behind Schedule', class: 'negative' };
            } else if (efficiency < 50) {
                return { text: 'Fairly Behind Schedule', class: 'warning' };
            } else if (efficiency < 75) {
                return { text: 'On Track', class: 'neutral' };
            } else {
                return { text: 'Highly Efficient', class: 'positive' };
            }
        };

        // Function to show metric information in a modal
        $scope.showMetricInfo = function(metricType) {
            const metricInfo = {
                taskEfficiency: {
                    title: 'Task Completion Efficiency',
                    description: 'Measures how often tasks are completed within their estimated timeframe.',
                    calculation: 'Task Completion Efficiency = (Tasks completed on or before their end date) ÷ (Total tasks assigned to the user)',
                    interpretation: 'Higher values indicate better time management and efficiency.'
                },
                avgCompletionTime: {
                    title: 'Average Task Completion Time',
                    description: 'The average time taken to complete tasks, excluding weekends.',
                    calculation: 'Calculated as the average working days between task assignment and completion.',
                    interpretation: 'Lower values indicate faster task completion.'
                },
                projectsCompleted: {
                    title: 'Projects Completed',
                    description: 'The total number of projects successfully completed by the user.',
                    calculation: 'Count of distinct projects where all assigned tasks are marked as completed.',
                    interpretation: 'Higher values indicate greater project contribution.'
                },
                completionRate: {
                    title: 'Task Completion Bar',
                    description: 'Shows how close a user is to finishing all assigned tasks.',
                    calculation: 'Completed Tasks - Total Assigned Tasks',
                    interpretation: 'Progress of completed tasks out of total assigned tasks.'
                },
                productivityRate: {
                    title: 'Productivity Rate',
                    description: 'A combined metric of efficiency and completion rate.',
                    calculation: '(Task Completion Efficiency × Completion Rate) / 100',
                    interpretation: 'Higher values indicate overall better productivity.'
                },
                overallGrowth: {
                    title: 'Overall Growth',
                    description: 'A comprehensive performance score combining multiple metrics.',
                    calculation: '40% (Efficiency) + 40% (Completion Rate) + 20% (Projects Completed)',
                    interpretation: 'Higher values indicate better overall performance growth.'
                }
            };

            const info = metricInfo[metricType];

            Swal.fire({
                title: `<strong>${info.title}</strong>`,
                icon: 'info',
                html: `
                    <div class="text-left">
                        <p class="mb-3">${info.description}</p>
                        <p class="mb-2"><strong>How it's calculated:</strong></p>
                        <p class="mb-3">${info.calculation}</p>
                        <p class="mb-2"><strong>interpretation:</strong></p>
                        <p>${info.interpretation}</p>
                    </div>
                `,
                showCloseButton: true,
                showCancelButton: false,
                focusConfirm: false,
                confirmButtonText: 'Got it!',
                confirmButtonColor: '#208AAE',
                customClass: {
                    popup: 'metric-info-popup',
                    title: 'metric-info-title'
                },
                background: '#ffffff',
                backdrop: `
                    rgba(32,138,174,0.1)
                    url("/images/nyan-cat.gif")
                    left top
                    no-repeat
                `
            });
        };

        // Fetch user details and metrics
        $scope.fetchUserDetails = function() {
            if (!$scope.user_id) {
                console.error('No user ID provided');
                return;
            }

            console.log('Fetching data with params:', {
                id: $scope.user_id,
                start_date: $scope.fromDate,
                end_date: $scope.toDate
            });

            $http.get('apiSheet/get_user_details.php', {
                params: {
                    id: $scope.user_id,
                    start_date: $scope.fromDate,
                    end_date: $scope.toDate
                }
            }).then(function(response) {
                console.log('User Details Response:', response.data);
                if (response.data.success) {
                    $scope.user = { name: response.data.user_name };
                    $scope.userName = response.data.user_name || $scope.userName;

                    // Update metrics with real data
                    if (response.data.task_completion_efficiency !== undefined) {
                        $scope.metrics.taskCompletionEfficiency = response.data.task_completion_efficiency;
                        $scope.metrics.efficiencyStatus = $scope.getEfficiencyStatus(response.data.task_completion_efficiency);
                    }
                    if (response.data.completed_tasks !== undefined && response.data.total_tasks !== undefined) {
                        $scope.metrics.completedTasks = response.data.completed_tasks;
                        $scope.metrics.totalTasks = response.data.total_tasks;
                        $scope.metrics.completionRate = response.data.completion_rate;
                    }
                    if (response.data.avg_completion_time !== undefined) {
                        $scope.metrics.avgCompletionTime = response.data.avg_completion_time;
                    }
                    if (response.data.projects_completed !== undefined) {
                        $scope.metrics.projectsCompleted = response.data.projects_completed;
                    }
                    if (response.data.total_projects !== undefined) {
                        $scope.metrics.totalProjects = response.data.total_projects;
                    }
                    if (response.data.productivity_rate !== undefined) {
                        $scope.metrics.productivityRate = response.data.productivity_rate;
                    }
                    if (response.data.overall_growth !== undefined) {
                        $scope.metrics.overallGrowth = response.data.overall_growth;
                    }

                    // Update charts with new data
                    $scope.updateCharts();
                } else {
                    console.error('Error fetching user details:', response.data.message);
                    $scope.userName = $scope.userName || 'Unknown User';
                }
            }, function(error) {
                console.error('HTTP error:', error);
                console.error('Error details:', error.data);
                $scope.userName = $scope.userName || 'Unknown User';
            });
        };

        // Function to update charts
        $scope.updateCharts = function() {
            // Task Performance Metrics Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: ['Task Efficiency', 'Completion Rate', 'Productivity Rate', 'Overall Growth'],
                    datasets: [{
                        label: 'Performance Metrics',
                        data: [
                            $scope.metrics.taskCompletionEfficiency,
                            $scope.metrics.completionRate,
                            $scope.metrics.productivityRate,
                            $scope.metrics.overallGrowth
                        ],
                        backgroundColor: [
                            'rgba(94, 114, 228, 0.8)',
                            'rgba(94, 114, 228, 0.7)',
                            'rgba(94, 114, 228, 0.6)',
                            'rgba(94, 114, 228, 0.5)'
                        ],
                        borderColor: [
                            'rgba(94, 114, 228, 1)',
                            'rgba(94, 114, 228, 1)',
                            'rgba(94, 114, 228, 1)',
                            'rgba(94, 114, 228, 1)'
                        ],
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Completion Overview Chart
            const completionCtx = document.getElementById('completionChart').getContext('2d');
            new Chart(completionCtx, {
                type: 'pie',
                data: {
                    labels: ['Completed Tasks', 'Incomplete Tasks', 'Completed Projects', 'Incomplete Projects'],
                    datasets: [{
                        data: [
                            $scope.metrics.completedTasks,
                            $scope.metrics.totalTasks - $scope.metrics.completedTasks,
                            $scope.metrics.projectsCompleted,
                            $scope.metrics.totalProjects - $scope.metrics.projectsCompleted
                        ],
                        backgroundColor: [
                            'rgba(94, 114, 228, 0.8)',
                            'rgba(94, 114, 228, 0.4)',
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(40, 167, 69, 0.4)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed;
                                }
                            }
                        }
                    }
                }
            });
        };

        // Initialize controller
        $scope.init = function() {
            if ($scope.user_info.profile_pic) {
                $scope.profile_pic_true = true;
                $scope.profile_pic = $scope.user_info.profile_pic;
            }
            $scope.fetchUserDetails();
        };

        $scope.init();
    }]);