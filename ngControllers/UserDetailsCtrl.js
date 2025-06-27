angular.module('sheetApp')
    .controller('UserDetailsCtrl', function($scope, $http, $location) {
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
        $scope.userName = $location.search().name || ''; // New variable for URL parameter

        // Fetch user details
        $scope.fetchUserDetails = function() {
            if (!$scope.user_id) {
                console.error('No user ID provided');
                return;
            }
            $http.get('apiSheet/get_user_details.php', {
                params: { id: $scope.user_id }
            }).then(function(response) {
                console.log('User Details Response:', response.data);
                if (response.data.success) {
                    $scope.user = { name: response.data.user_name };
                    // If no name from URL, use the one from API
                    if (!$scope.userName) {
                        $scope.userName = response.data.user_name;
                    }
                } else {
                    console.error('Error fetching user details:', response.data.message);
                    // Fallback to URL parameter or default
                    $scope.userName = $scope.userName || 'Unknown User';
                }
            }, function(error) {
                console.error('HTTP error:', error);
                // Fallback to URL parameter or default
                $scope.userName = $scope.userName || 'Unknown User';
            });
        };

        // Logout function
        $scope.logout = function() {
            alert('Logout clicked (implement later)');
        };

        // Initialize controller
        $scope.init = function() {
            if ($scope.user_info.profile_pic) {
                $scope.profile_pic_true = true;
                $scope.profile_pic = $scope.user_info.profile_pic;
            }
            // If we have name from URL, use it immediately
            if ($scope.userName) {
                $scope.user.name = $scope.userName;
            }
            $scope.fetchUserDetails();
        };

        $scope.init();
    });