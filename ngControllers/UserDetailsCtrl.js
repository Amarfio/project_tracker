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
            efficiencyChange: 0,
            efficiencyStatus: { text: 'No Data', class: 'neutral' },
            avgCompletionTime: 0,
            completionTimeChange: 0,
            projectsImplemented: 0,
            projectsChange: 0,
            completedTasks: 0,
            totalTasks: 0,
            completionRate: 0,
            productivityRate: 0,
            productivityChange: 0,
            overallGrowth: 0,
            growthChange: 0
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
                        $scope.metrics.completionRate = response.data.total_tasks > 0 ?
                            Math.round((response.data.completed_tasks / response.data.total_tasks) * 100) : 0;
                    }
                    if (response.data.avg_completion_time !== undefined) {
                        $scope.metrics.avgCompletionTime = response.data.avg_completion_time;
                    }

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