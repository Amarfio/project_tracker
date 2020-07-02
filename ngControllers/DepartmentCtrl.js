sheetApp.controller('DepartmentCtrl', function ($scope, $http, check_auth, myConfig, $location, $localStorage) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)

    $scope.users_prifile_path = myConfig.file_url


    //logout funtion 
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function

    $scope.get_departments = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllDepartments.php'

        }).then(function successCallback(response) {

            $scope.departments = response.data
            console.log($scope.departments)

        }, function errorCallback(response) {

            alert("Error. Try Again!");

        });
    }
    $scope.get_departments()

    $scope.return_to_department = function () {
        $('#department_detail').hide();
        $('.return_to_department').hide();
        $('#department_list').show();
        $('a#route_title').show();
    }

    $scope.view_user = function (user) {
        console.log(user)
        $scope.department_user_info = user
    }


    $scope.get_department_users = function (department_id) {
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllUsers.php?department_id=' + department_id

        }).then(function successCallback(response) {

            $scope.department_users = response.data;
            console.log($scope.department_users)

        }, function errorCallback(response) {
            Swal.fire({
                type: 'warning',
                title: 'Network Connection Erro',
                text: 'Users Could not loaded'
            })

        });
    }


    $scope.view_department = function (department_id, department_init_desc, department_desc) {
        console.log(department_id)
        console.log(department_init_desc)
        console.log(department_desc)
        $scope.route_title = department_desc
        $('#department_detail').show();
        $('.return_to_department').show();
        $('#department_list').hide();
        $('a#route_title').hide();
        $scope.get_department_users(department_id);

    }



});