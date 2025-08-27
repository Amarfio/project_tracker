sheetApp.controller("StatisticsnewCtrl", function($scope, $http, $location, $timeout, $localStorage, check_auth, myConfig) {
    // Verify authentication and load user info from localStorage
    check_auth.verify_auth($localStorage.user_info);
    $scope.user_info = $localStorage.user_info.data;

    // Profile photo handling
    $scope.profile_pic_true = $localStorage.profile_pic;
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true;

    // Date range variables
    $scope.fromDate = localStorage.getItem('fromDate') ? new Date(localStorage.getItem('fromDate')) : new Date(new Date().getFullYear(), 0, 1);
    $scope.toDate = localStorage.getItem('toDate') ? new Date(localStorage.getItem('toDate')) : new Date();

    // Initialize stat_cards
    $scope.updateCardDescriptions = function() {
        $scope.stat_cards = [
            { id: 1, description: `Total Projects`, value: 0, icon: "ni ni-chart-bar-32" },
            { id: 2, description: `Completed Projects`, value: 0, icon: "ni ni-check-bold" },
            { id: 5, description: `Total Tasks`, value: 0, icon: "ni ni-chart-bar-32" },
            { id: 6, description: `Completed Tasks`, value: 0, icon: "ni ni-check-bold" }
        ];
    };

    // Helper function to format date for API
    $scope.formatDateForAPI = function(date) {
        return date ? new Date(date).toISOString().split('T')[0] : '';
    };

    // Function to fetch statistics from API
    $scope.fetchStatistics = function() {
        if (!$scope.fromDate || !$scope.toDate) {
            console.error('Please select both from and to dates');
            return;
        }

        // Save to localStorage
        localStorage.setItem('fromDate', $scope.fromDate);
        localStorage.setItem('toDate', $scope.toDate);

        // Format dates for API
        var fromDateStr = $scope.formatDateForAPI($scope.fromDate);
        var toDateStr = $scope.formatDateForAPI($scope.toDate);

        console.log('Fetching statistics for date range:', fromDateStr, 'to', toDateStr);
        $http.get('apiSheet/get_statistics.php', {
            params: {
                start_date: fromDateStr,
                end_date: toDateStr
            }
        }).then(function(response) {
            console.log('Statistics Response:', response.data);
            if (response.data.success) {
                $scope.tasks = response.data.table_data;
                $scope.stat_cards = $scope.stat_cards.map(function(card, index) {
                    return {
                        id: card.id,
                        description: card.description,
                        value: response.data.card_data[index].value,
                        icon: card.icon
                    };
                });
                $timeout(function() {
                    $scope.$apply();
                });
            } else {
                console.error('Error fetching statistics:', response.data.message);
                $scope.tasks = [];
                $scope.stat_cards.forEach(function(card) { card.value = 0; });
            }
        }, function(error) {
            console.error('HTTP error:', error);
            $scope.tasks = [];
            $scope.stat_cards.forEach(function(card) { card.value = 0; });
        });
    };

    // Function to navigate to task details page
    $scope.viewTaskDetails = function(department, metric) {
        console.log('Navigating to task details:', {
            department: department,
            metric: metric,
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate)
        });
        $location.path('/task_details').search({
            department: department,
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate),
            metric: metric
        });
    };

    // Function to navigate to analysis page
    $scope.navigateToAnalysis = function(analysisType) {
        var path = '';
        if (analysisType === 'individual_tasks') {
            path = '/tasks_analysis';
        } else if (analysisType === 'department_performance') {
            path = '/department_performance';
        } else if (analysisType === 'task_timelines') {
            path = '/task_delivery_insights';
        } else if (analysisType === 'comparison_tool') {
            path = '/comparison';
        } else {
            path = '/tasks_analysis';
        }
        $location.path(path).search({
            type: analysisType,
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate)
        });
    };

    // Function to navigate directly to comparison page
    $scope.navigateToComparison = function() {
        $location.path('/comparison').search({
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate)
        });
    };

    // Logout function
    $scope.logout = function() {
        check_auth.logout($scope.user_info.user_id);
    };

    // Initialize controller
    $scope.init = function() {
        if ($scope.user_info.profile_pic) {
            $scope.profile_pic_true = true;
            $scope.profile_pic = myConfig.file_url + $scope.user_info.profile_pic;
        } else {
            $scope.profile_pic_true = null;
        }

        $scope.updateCardDescriptions();
        $timeout(function() {
            $scope.fetchStatistics();
        }, 0);
    };

    $scope.init();
});

// Enhanced flatpickr directive
sheetApp.directive('flatpickr', ['$timeout', '$document', '$window', function($timeout, $document, $window) {
    return {
        restrict: 'A',
        require: 'ngModel',
        scope: {
            minDate: '=?',
            maxDate: '=?'
        },
        link: function(scope, element, attrs, ngModel) {
            var fp;
            var options = {
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr) {
                    $timeout(function() {
                        ngModel.$setViewValue(dateStr);
                    });
                }
            };

            function initializeFlatpickr() {
                if (attrs.minDate) {
                    options.minDate = new Date(scope.minDate);
                }
                if (attrs.maxDate) {
                    options.maxDate = new Date(scope.maxDate);
                }
                fp = flatpickr(element[0], options);
                ngModel.$render = function() {
                    if (ngModel.$viewValue) {
                        fp.setDate(ngModel.$viewValue);
                    }
                };
                if (attrs.minDate) {
                    scope.$watch('minDate', function(newVal) {
                        if (newVal) {
                            options.minDate = new Date(newVal);
                            if (fp) {
                                fp.set('minDate', options.minDate);
                            }
                        }
                    });
                }
                if (attrs.maxDate) {
                    scope.$watch('maxDate', function(newVal) {
                        if (newVal) {
                            options.maxDate = new Date(newVal);
                            if (fp) {
                                fp.set('maxDate', options.maxDate);
                            }
                        }
                    });
                }
            }

            function loadFlatpickr() {
                if (typeof $window.flatpickr === 'undefined') {
                    var script = $document[0].createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
                    script.onload = function() {
                        var link = $document[0].createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
                        $document[0].head.appendChild(link);
                        initializeFlatpickr();
                    };
                    $document[0].body.appendChild(script);
                } else {
                    initializeFlatpickr();
                }
            }

            scope.$on('$destroy', function() {
                if (fp) {
                    fp.destroy();
                }
            });

            loadFlatpickr();
        }
    };
}]);