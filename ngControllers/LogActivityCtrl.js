sheetApp.controller('LogActivityCtrl', function ($scope, $http, check_auth, myConfig, $location, $localStorage) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)



    //logout funtion 
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function 



    $scope.get_all_log_activities = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllLogActivity.php'

        }).then(function successCallback(response) {

            $scope.all_log_activities = response.data;
            console.log($scope.all_log_activities)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }
    $scope.get_all_log_activities()

    $scope.view_log = function (log) {
        console.log(log)
        $scope.log_details = log
    }


});