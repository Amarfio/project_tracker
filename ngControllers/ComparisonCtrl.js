angular.module('sheetApp').controller('ComparisonCtrl', ['$scope', '$http', '$location', '$timeout', function($scope, $http, $location, $timeout) {
    // Initialize variables
    $scope.comparisonType = 'individual'; // Default to individuals
    $scope.loading = false;
    $scope.error = null;
    $scope.users = [];
    $scope.departments = [
        { id: 110, name: 'Credits' },
        { id: 106, name: 'Enterprise Solutions' },
        { id: 130, name: 'Mobile Operations' },
        { id: 129, name: 'Mobile Technologies' },
        { id: 105, name: 'Operation' },
        { id: 109, name: 'Systems' },
        { id: 112, name: 'Treasury/Payment' }
    ];
    $scope.leftSelection = null;
    $scope.rightSelection = null;
    $scope.leftMetrics = {
        totalTasks: null,
        totalCompletedTasks: null,
        taskCompletionEfficiency: null,
        avgCompletionTime: null,
        projectsCompleted: null,
        productivityRate: null,
        overallGrowth: null
    };
    $scope.rightMetrics = {
        totalTasks: null,
        totalCompletedTasks: null,
        taskCompletionEfficiency: null,
        avgCompletionTime: null,
        projectsCompleted: null,
        productivityRate: null,
        overallGrowth: null
    };
    $scope.leftMetricsLoading = false;
    $scope.rightMetricsLoading = false;
    $scope.fromDate = null;
    $scope.toDate = null;

    // Format date for API (YYYY-MM-DD)
    $scope.formatDateForAPI = function(date) {
        if (!date) return '';
        return date.toISOString().split('T')[0];
    };

    // Parse URL parameters for date range
    $scope.parseUrlParams = function() {
        var params = $location.search();
        $scope.fromDate = params.start_date ? new Date(params.start_date) : null;
        $scope.toDate = params.end_date ? new Date(params.end_date) : null;
        console.log('Parsed dates:', { fromDate: $scope.fromDate, toDate: $scope.toDate });
    };

    // Set comparison type (individuals or department)
    $scope.setComparisonType = function(type) {
        $scope.comparisonType = type;
        $scope.leftSelection = null;
        $scope.rightSelection = null;
        $scope.leftMetrics = {
            totalTasks: null,
            totalCompletedTasks: null,
            taskCompletionEfficiency: null,
            avgCompletionTime: null,
            projectsCompleted: null,
            productivityRate: null,
            overallGrowth: null
        };
        $scope.rightMetrics = {
            totalTasks: null,
            totalCompletedTasks: null,
            taskCompletionEfficiency: null,
            avgCompletionTime: null,
            projectsCompleted: null,
            productivityRate: null,
            overallGrowth: null
        };
        console.log('Comparison type set to:', type);
        $timeout(function() {
            $scope.$apply(); // Ensure view updates
        });
    };

    // Fetch active users
    $scope.fetchUsers = function() {
        $scope.loading = true;
        console.log('Fetching users from: /project_tracker_test/apiSheet/comparison_api.php?action=get_users');
        $http.get('/project_tracker_test/apiSheet/comparison_api.php?action=get_users')
            .then(function(response) {
                console.log('API response (users):', response);
                if (response.data && typeof response.data === 'object' && response.data.success) {
                    if (response.data.data && response.data.data.length > 0) {
                        $scope.users = response.data.data.map(user => {
                            if (!user.id || !user.f_name || !user.l_name) {
                                console.warn('Invalid user data:', user);
                                return null;
                            }
                            // Skip TEST USER
                            if (user.f_name === 'TEST' && user.l_name === 'USER') {
                                console.log('Skipping TEST USER:', user);
                                return null;
                            }
                            return {
                                id: user.id,
                                name: `${user.f_name} ${user.l_name}`
                            };
                        }).filter(user => user !== null);
                        console.log('Processed users:', $scope.users);
                        if ($scope.users.length === 0) {
                            $scope.error = 'No valid user data returned from API';
                            console.log('API warning: No valid user data');
                        }
                    } else {
                        $scope.error = 'No users found in API response';
                        console.log('API warning: Empty data array');
                    }
                } else {
                    $scope.error = response.data && response.data.message ? response.data.message : 'Invalid API response';
                    console.log('API error:', $scope.error);
                }
                $scope.loading = false;
                $timeout(function() {
                    $scope.$apply(); // Ensure view updates
                });
            })
            .catch(function(error) {
                $scope.error = 'Failed to fetch users: ' + (error.message || error.statusText || 'Unknown error');
                $scope.loading = false;
                console.error('Error fetching users:', error);
                $timeout(function() {
                    $scope.$apply();
                });
            });
    };

    // Fetch metrics for a selection (individual or department)
    $scope.fetchMetrics = function(selection, side) {
        if (!selection || !$scope.fromDate || !$scope.toDate) {
            console.log('Skipping metrics fetch: Missing selection or dates', { selection, fromDate: $scope.fromDate, toDate: $scope.toDate });
            return;
        }

        var isIndividual = $scope.comparisonType === 'individual';
        var params = {
            action: 'get_total_tasks',
            type: isIndividual ? 'individual' : 'department',
            id: selection.id,
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate)
        };

        if (side === 'left') {
            $scope.leftMetricsLoading = true;
            $scope.leftMetrics = {
                totalTasks: null,
                totalCompletedTasks: null,
                taskCompletionEfficiency: null,
                avgCompletionTime: null,
                projectsCompleted: null,
                productivityRate: null,
                overallGrowth: null
            };
        } else {
            $scope.rightMetricsLoading = true;
            $scope.rightMetrics = {
                totalTasks: null,
                totalCompletedTasks: null,
                taskCompletionEfficiency: null,
                avgCompletionTime: null,
                projectsCompleted: null,
                productivityRate: null,
                overallGrowth: null
            };
        }

        console.log('Fetching metrics for', side, ':', params);
        $http.get('/project_tracker_test/apiSheet/comparison_api.php', { params: params })
            .then(function(response) {
                console.log('Metrics response (' + side + '):', response);
                if (response.data && typeof response.data === 'object' && response.data.success) {
                    var metrics = {
                        totalTasks: response.data.data.total_tasks || 0,
                        totalCompletedTasks: response.data.data.total_completed_tasks || 0,
                        taskCompletionEfficiency: response.data.data.task_completion_efficiency || 0,
                        avgCompletionTime: response.data.data.avg_completion_time || 0,
                        projectsCompleted: response.data.data.projects_completed || 0,
                        productivityRate: response.data.data.productivity_rate || 0,
                        overallGrowth: response.data.data.overall_growth || 0
                    };
                    if (side === 'left') {
                        $scope.leftMetrics = metrics;
                        $scope.leftMetricsLoading = false;
                    } else {
                        $scope.rightMetrics = metrics;
                        $scope.rightMetricsLoading = false;
                    }
                } else {
                    $scope.error = response.data && response.data.message ? response.data.message : 'Invalid metrics response';
                    console.log('Metrics error (' + side + '):', $scope.error);
                    if (side === 'left') {
                        $scope.leftMetricsLoading = false;
                    } else {
                        $scope.rightMetricsLoading = false;
                    }
                }
                $timeout(function() {
                    $scope.$apply();
                });
            })
            .catch(function(error) {
                $scope.error = 'Failed to fetch metrics for ' + side + ': ' + (error.message || error.statusText || 'Unknown error');
                console.error('Error fetching metrics (' + side + '):', error);
                if (side === 'left') {
                    $scope.leftMetricsLoading = false;
                } else {
                    $scope.rightMetricsLoading = false;
                }
                $timeout(function() {
                    $scope.$apply();
                });
            });
    };

    // Update selections
    $scope.updateLeftSelection = function() {
        console.log('Left selection updated:', $scope.leftSelection);
        $scope.fetchMetrics($scope.leftSelection, 'left');
    };

    $scope.updateRightSelection = function() {
        console.log('Right selection updated:', $scope.rightSelection);
        $scope.fetchMetrics($scope.rightSelection, 'right');
    };

    // Initialize controller
    $scope.init = function() {
        $scope.parseUrlParams();
        $scope.fetchUsers();
    };

    $scope.init();
}]);