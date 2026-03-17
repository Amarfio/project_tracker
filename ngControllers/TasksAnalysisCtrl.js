sheetApp.service('DateRangeService', function () {
    var dateRange = {
        startDate: null,
        endDate: null
    };
    return {
        setDateRange: function (start, end) {
            dateRange.startDate = start ? new Date(start) : null;
            dateRange.endDate = end ? new Date(end) : null;
            localStorage.setItem('fromDate', start ? new Date(start).toISOString().split('T')[0] : '');
            localStorage.setItem('toDate', end ? new Date(end).toISOString().split('T')[0] : '');
        },
        getDateRange: function () {
            return {
                startDate: dateRange.startDate ? new Date(dateRange.startDate) : null,
                endDate: dateRange.endDate ? new Date(dateRange.endDate) : null
            };
        }
    };
});

sheetApp.controller("TasksAnalysisCtrl", function ($scope, $http, $location, $timeout, DateRangeService) {
    // Initialize chart instances
    var efficiencyChartInstance = null;
    var trendChartInstance = null;
    var deliveryPieChartInstance = null;
    var deliveryTrendChartInstance = null;

    // Determine page type based on URL path
    $scope.isDepartmentPerformance = $location.path().includes('department_performance');
    $scope.isTaskDeliveryInsights = $location.path().includes('task_delivery_insights');
    $scope.analysisType = $scope.isDepartmentPerformance ? 'department_performance' : ($scope.isTaskDeliveryInsights ? 'task_timelines' : ($location.search().type || 'individual_tasks'));

    // Initialize dates from localStorage or URL
    var urlStartDate = $location.search().start_date;
    var urlEndDate = $location.search().end_date;
    var storedFromDate = localStorage.getItem('fromDate');
    var storedToDate = localStorage.getItem('toDate');

    $scope.fromDate = urlStartDate ? new Date(urlStartDate) : (storedFromDate ? new Date(storedFromDate) : new Date(new Date().getFullYear(), 0, 1));
    $scope.toDate = urlEndDate ? new Date(urlEndDate) : (storedToDate ? new Date(storedToDate) : new Date());

    // Store dates in service
    DateRangeService.setDateRange($scope.fromDate, $scope.toDate);

    $scope.searchQuery = '';
    $scope.tasks = [];
    $scope.filteredTasks = [];
    $scope.departments = {};
    $scope.filteredDepartments = [];
    $scope.deliveryData = {};

    // Format date for API
    $scope.formatDateForAPI = function (date) {
        return date ? new Date(date).toISOString().split('T')[0] : '';
    };

    // Calculate delay percentage for severity indicator
    $scope.getDelayPercentage = function (delay) {
        // Cap at 100 days for visualization
        const cappedDelay = Math.min(delay, 100);
        return (cappedDelay / 100) * 100;
    };

    // Function to show metric information in a modal
    $scope.showMetricInfo = function (metricType) {
        const metricInfo = {
            departmentTaskOverview: {
                title: 'Department Task Overview',
                description: 'Displays the average task cycle time for each department, showing how long tasks take from approval to completion.',
                calculation: 'Average task cycle time is calculated as the average number of days between project approval and task completion (or last update) for completed tasks.',
                interpretation: 'Lower cycle times indicate faster task completion within the department.'
            },
            departmentEfficiencyScores: {
                title: 'Department Efficiency Scores',
                description: 'Shows the efficiency of task completion across departments, measured by tasks completed on or before their deadlines.',
                calculation: 'Efficiency score = (Number of tasks completed on or before deadline / Total tasks) × 100.',
                interpretation: 'Higher scores indicate better adherence to task deadlines.'
            },
            underperformingIndividuals: {
                title: 'Underperforming Individuals',
                description: 'Lists individuals with overdue tasks, including their department, number of overdue tasks, average delay, and completion rate.',
                calculation: 'Overdue tasks are those completed after their deadline. Average delay is the mean days past deadline for overdue tasks. Completion rate = (Completed tasks / Total tasks) × 100.',
                interpretation: 'Identifies individuals needing support to improve task completion timeliness.'
            },
            performanceTrends: {
                title: 'Performance Trends Over Time',
                description: 'Tracks department completion rates over time, showing monthly task completion performance.',
                calculation: 'Monthly completion rate = (Completed tasks in the month / Total tasks in the month) × 100.',
                interpretation: 'Trends show how department performance evolves, with higher rates indicating improved performance.'
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
                    <p class="mb-2"><strong>How to interpret:</strong></p>
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

    // Initialize charts for department performance
    $scope.initCharts = function (data) {
        if (!$scope.isDepartmentPerformance || !data || !data.departments) return;

        console.log('Initializing department charts with data:', JSON.stringify(data, null, 2));

        // Destroy existing charts
        if (efficiencyChartInstance) efficiencyChartInstance.destroy();
        if (trendChartInstance) trendChartInstance.destroy();

        // Efficiency Chart
        var ctx1 = document.getElementById('efficiencyChart');
        if (ctx1) {
            efficiencyChartInstance = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: data.departments.map(dept => dept.department),
                    datasets: [{
                        label: 'Efficiency Score',
                        data: data.departments.map(dept => dept.task_completion_efficiency),
                        backgroundColor: '#5e72e4'
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Efficiency Score (%)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Department'
                            }
                        }
                    }
                }
            });
            console.log('Efficiency chart initialized');
        } else {
            console.error('efficiencyChart canvas not found');
        }

        // Trends Chart
        var ctx2 = document.getElementById('trendChart');
        if (ctx2 && data.trends && data.trends.months) {
            var card = ctx2.parentElement.parentElement;
            var cardHeight = card.clientHeight || 400;
            var cardWidth = card.clientWidth || 600;
            ctx2.style.height = Math.max(400, cardHeight - 50) + 'px';
            ctx2.style.width = (cardWidth - 40) + 'px';
            ctx2.style.maxHeight = 'none';

            var start = new Date($scope.fromDate);
            var end = new Date($scope.toDate);
            var months = [];
            for (var d = new Date(start); d <= end; d.setMonth(d.getMonth() + 1)) {
                months.push(d.toISOString().slice(0, 7));
            }

            var deptNames = data.departments.map(dept => dept.department);
            var colors = [
                '#5e72e4', '#11cdef', '#2dce89', '#fb6340', '#f5365c',
                '#172b4d', '#d81b60', '#ffd600', '#00c4b4', '#8e44ad',
                '#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6'
            ];

            var datasets = data.departments.map((dept, index) => {
                var dataPoints = months.map(month => {
                    var deptKey = dept.department.toLowerCase();
                    var monthIndex = data.trends.months.indexOf(month);
                    return monthIndex !== -1 && data.trends[deptKey] && typeof data.trends[deptKey][monthIndex] === 'number' ?
                        data.trends[deptKey][monthIndex] :
                        0;
                });
                return {
                    label: dept.department,
                    data: dataPoints,
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length],
                    pointBackgroundColor: colors[index % colors.length],
                    pointBorderColor: '#ffffff',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 2.5,
                    fill: false,
                    tension: 0.3
                };
            });

            var monthLabels = months.map(month => {
                var date = new Date(month + '-01');
                return date.toLocaleString('default', { month: 'short', year: 'numeric' });
            });

            trendChartInstance = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 30,
                            left: 30,
                            right: 30
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 11 },
                                padding: 20,
                                boxWidth: 14,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    var value = context.parsed.y;
                                    return context.dataset.label + ': ' + value.toFixed(2) + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Completion Rate (%)',
                                font: { size: 14 }
                            },
                            ticks: {
                                font: { size: 12 },
                                callback: function (value) { return value + '%'; }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month',
                                font: { size: 14 }
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
            console.log('Trends chart initialized with ' + datasets.length + ' datasets and ' + monthLabels.length + ' months');
        } else {
            console.error('trendChart canvas not found or trends data missing');
        }

        $scope.$applyAsync();
    };

    // Initialize charts for task delivery insights
    $scope.initDeliveryCharts = function () {
        if (!$scope.isTaskDeliveryInsights || !$scope.deliveryData) {
            console.error('Not initializing delivery charts: isTaskDeliveryInsights=' + $scope.isTaskDeliveryInsights + ', deliveryData=' + JSON.stringify($scope.deliveryData));
            return;
        }

        console.log('Initializing delivery charts with data:', JSON.stringify($scope.deliveryData, null, 2));

        $timeout(function () {
            // Pie Chart for On-time vs Late Deliveries
            var pieCtx = document.getElementById('deliveryPieChart');
            if (pieCtx) {
                if (deliveryPieChartInstance) {
                    deliveryPieChartInstance.destroy();
                }

                var onTimeCount = $scope.deliveryData.on_time_tasks || 0;
                var lateCount = $scope.deliveryData.late_tasks || 0;
                var total = onTimeCount + lateCount;
                var onTimePercentage = total > 0 ? Math.round((onTimeCount / total) * 100) : 0;
                var latePercentage = total > 0 ? Math.round((lateCount / total) * 100) : 0;

                // Use the built-in Chart.js datalabels plugin
                deliveryPieChartInstance = new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: ['On-time', 'Late'],
                        datasets: [{
                            data: [onTimeCount, lateCount],
                            backgroundColor: ['#68d391', '#fc8181'],
                            borderColor: ['#ffffff', '#ffffff'],
                            borderWidth: 2,
                            borderRadius: 8
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
                                    label: function (context) {
                                        var label = context.label || '';
                                        var value = context.raw || 0;
                                        var percentage = Math.round((value / total) * 100);
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            },
                            datalabels: {
                                display: true,
                                color: '#fff',
                                font: {
                                    weight: 'bold',
                                    size: 14
                                },
                                formatter: (value, ctx) => {
                                    let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = (value * 100 / sum).toFixed(0) + '%';
                                    return percentage;
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true,
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
                console.log('Delivery Pie Chart initialized');
            } else {
                console.error('deliveryPieChart canvas not found');
            }

            // Line Chart for Delivery Trends
            var trendCtx = document.getElementById('deliveryTrendChart');
            if (trendCtx && $scope.deliveryData.trend_months && $scope.deliveryData.trend_delays) {
                if (deliveryTrendChartInstance) {
                    deliveryTrendChartInstance.destroy();
                }

                // Format month labels
                var monthLabels = $scope.deliveryData.trend_months.map(month => {
                    var date = new Date(month + '-01');
                    return date.toLocaleString('default', { month: 'short', year: 'numeric' });
                });

                deliveryTrendChartInstance = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: 'Delays (Days)',
                            data: $scope.deliveryData.trend_delays,
                            borderColor: '#5a67d8',
                            backgroundColor: '#5a67d8',
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#5a67d8',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 3,
                            tension: 0.4,
                            fill: false
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
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                cornerRadius: 8,
                                padding: 10
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Average Delay (Days)',
                                    font: { size: 12, family: 'Inter' },
                                    color: '#2b6cb0'
                                },
                                ticks: {
                                    font: { size: 10, family: 'Inter' },
                                    color: '#4a5568',
                                    maxTicksLimit: 6,
                                    stepSize: null
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month',
                                    font: { size: 12, family: 'Inter' },
                                    color: '#2b6cb0'
                                },
                                ticks: {
                                    font: { size: 10, family: 'Inter' },
                                    color: '#4a5568',
                                    maxTicksLimit: '6'
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 1200,
                            easing: 'easeOutCubic'
                        }
                    }
                });
                console.log('Delivery Trend Chart initialized with ' + monthLabels.length + ' months');
            } else {
                console.error('deliveryTrendChart canvas not found or trend data missing:', {
                    trendCtx: !!trendCtx,
                    trend_months: !!$scope.deliveryData.trend_months,
                    trend_delays: !!$scope.deliveryData.trend_delays
                });
            }
        });
    };

    // Watch for changes in deliveryData
    $scope.$watch('deliveryData', function (newData) {
        if (newData && $scope.isTaskDeliveryInsights) {
            console.log('deliveryData changed:', JSON.stringify(newData, null, 2));
            $scope.initDeliveryCharts();
        }
    }, true);

    // Fetch data based on analysis type
    $scope.fetchAnalysisData = function () {
        if (!$scope.fromDate || !$scope.toDate) {
            console.error('Please select both from and to dates');
            return;
        }

        // Update localStorage and service
        DateRangeService.setDateRange($scope.fromDate, $scope.toDate);

        var params = {
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate),
            type: $scope.analysisType
        };

        // Update URL with date range parameters
        $location.search({
            type: $scope.analysisType,
            start_date: params.start_date,
            end_date: params.end_date
        });

        console.log('Fetching analysis data with params:', JSON.stringify(params));

        $http.get('apiSheet/get_analysis_data.php', { params: params })
            .then(function (response) {
                console.log('API response:', JSON.stringify(response.data, null, 2));
                if (response.data.success) {
                    console.log('Analysis data loaded:', JSON.stringify(response.data.data, null, 2));
                    console.log('Date range:', params.start_date, params.end_date);
                    if ($scope.isDepartmentPerformance) {
                        $scope.departments = response.data.data;
                        $scope.filterDepartments();
                        $scope.initCharts($scope.departments);
                    } else if ($scope.isTaskDeliveryInsights) {
                        $scope.deliveryData = response.data.data;
                        $scope.$applyAsync(); // Ensure DOM updates
                    } else {
                        $scope.tasks = response.data.data;
                        $scope.filterTasks();
                    }
                } else {
                    console.error('API error:', response.data.message);
                    $scope.tasks = [];
                    $scope.filteredTasks = [];
                    $scope.departments = {};
                    $scope.filteredDepartments = [];
                    $scope.deliveryData = {};
                }
            }, function (error) {
                console.error('Error fetching analysis data:', error);
                $scope.tasks = [];
                $scope.filteredTasks = [];
                $scope.departments = {};
                $scope.filteredDepartments = [];
                $scope.deliveryData = {};
            });
    };

    // Filter tasks based on search query
    $scope.sortColumn = 'task_completion_efficiency';
    $scope.sortReverse = true;

    $scope.sortBy = function (column) {
        if ($scope.sortColumn === column) {
            $scope.sortReverse = !$scope.sortReverse;
        } else {
            $scope.sortColumn = column;
            $scope.sortReverse = true;
        }
        $scope.filterTasks();
    };

    // Filter and Sort tasks based on search query
    $scope.filterTasks = function () {
        if ($scope.analysisType !== 'individual_tasks') {
            $scope.filteredTasks = $scope.tasks;
            return;
        }
        var query = ($scope.searchQuery || '').toLowerCase();
        var tasksToFilter = $scope.tasks || [];

        if (query) {
            tasksToFilter = tasksToFilter.filter(function (task) {
                return (
                    (task.user || '').toLowerCase().includes(query) ||
                    (task.department || '').toLowerCase().includes(query) ||
                    (task.role || '').toLowerCase().includes(query) ||
                    (task.email || '').toLowerCase().includes(query)
                );
            });
        }

        // Apply sorting
        $scope.filteredTasks = tasksToFilter.slice().sort(function (a, b) {
            var valA = a[$scope.sortColumn];
            var valB = b[$scope.sortColumn];

            // Handle string comparison
            if (typeof valA === 'string') {
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            }

            if (valA < valB) return $scope.sortReverse ? 1 : -1;
            if (valA > valB) return $scope.sortReverse ? -1 : 1;

            // TIE-BREAKERS
            // Tier 1: Efficiency
            if ($scope.sortColumn !== 'task_completion_efficiency') {
                if (a.task_completion_efficiency < b.task_completion_efficiency) return 1;
                if (a.task_completion_efficiency > b.task_completion_efficiency) return -1;
            }

            // Tier 2: Task Count
            if ($scope.sortColumn !== 'task_count') {
                if (a.task_count < b.task_count) return 1;
                if (a.task_count > b.task_count) return -1;
            }

            // Tier 3: Avg Completion Time (Ascending)
            if ($scope.sortColumn !== 'avg_completion_days') {
                if (a.avg_completion_days < b.avg_completion_days) return -1;
                if (a.avg_completion_days > b.avg_completion_days) return 1;
            }

            // Tier 4: Name
            return strcmp(a.user, b.user);
        });
    };

    function strcmp(a, b) {
        a = a.toLowerCase();
        b = b.toLowerCase();
        if (a < b) return -1;
        if (a > b) return 1;
        return 0;
    }

    // Filter departments based on search query
    $scope.filterDepartments = function () {
        if ($scope.analysisType !== 'department_performance') {
            $scope.filteredDepartments = $scope.departments.departments || [];
            return;
        }
        var query = ($scope.searchQuery || '').toLowerCase();
        if (!query) {
            $scope.filteredDepartments = $scope.departments.departments || [];
            return;
        }
        $scope.filteredDepartments = ($scope.departments.departments || []).filter(function (dept) {
            return (dept.department || '').toLowerCase().includes(query);
        });
    };

    // Initialize
    $scope.fetchAnalysisData();
});