sheetApp.controller("StatisticsnewCtrl", function($scope, $http, $location, $timeout) {
    // Initialize scope variables
    $scope.user_info = {
        username: "Guest",
        role: "developer",
        gender: "Male",
        dept_id: 1
    };
    $scope.profile_pic_true = null;
    $scope.profile_pic = null;

    // Date range variables
    $scope.fromDate = localStorage.getItem('fromDate') ? new Date(localStorage.getItem('fromDate')) : new Date(new Date().getFullYear(), 0, 1); // Default to Jan 1 of current year
    $scope.toDate = localStorage.getItem('toDate') ? new Date(localStorage.getItem('toDate')) : new Date(); // Default to today

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
                // Force digest cycle to ensure UI updates
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
        $location.path('/tasks_analysis').search({
            type: analysisType,
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate)
        });
    };

    // Logout function (placeholder)
    $scope.logout = function() {
        alert("Logout clicked (implement later)");
    };

    // Initialize controller
    $scope.init = function() {
        // Set profile picture based on gender
        if ($scope.user_info.profile_pic) {
            $scope.profile_pic_true = true;
            $scope.profile_pic = $scope.user_info.profile_pic;
        } else {
            $scope.profile_pic_true = null;
        }

        // Update card descriptions and fetch statistics
        $scope.updateCardDescriptions();
        $timeout(function() {
            $scope.fetchStatistics();
        }, 0);
    };

    // Call initialization
    $scope.init();
});

// Enhanced flatpickr directive with availability check and dynamic loading
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
                // Set min/max dates if available
                if (attrs.minDate) {
                    options.minDate = new Date(scope.minDate);
                }
                if (attrs.maxDate) {
                    options.maxDate = new Date(scope.maxDate);
                }

                // Initialize flatpickr
                fp = flatpickr(element[0], options);

                // Update when model changes
                ngModel.$render = function() {
                    if (ngModel.$viewValue) {
                        fp.setDate(ngModel.$viewValue);
                    }
                };

                // Watch for min/max date changes
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
                    // Load flatpickr dynamically
                    var script = $document[0].createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
                    script.onload = function() {
                        // Also load the CSS
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

            // Clean up
            scope.$on('$destroy', function() {
                if (fp) {
                    fp.destroy();
                }
            });

            // Initialize
            loadFlatpickr();
        }
    };
}]);