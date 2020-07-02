sheetApp.controller('LogoutCtrl', function ($scope, $http, check_auth, myConfig, $location, $localStorage) {
    // check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data

    // // PROFILE PHOTO
    // $scope.profile_pic_true = $localStorage.profile_pic
    // $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    // console.log($scope.profile_pic)

    //logout funtion 
    check_auth.logout($scope.user_info.user_id)

    // end logout function 




});