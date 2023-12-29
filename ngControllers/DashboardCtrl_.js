sheetApp.controller('DashboardCtrl_', function ($scope, check_auth, myConfig, $http, $location, $localStorage, $location) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data
    $scope.user_role = $scope.user_info.role
    var user_role = $localStorage.user_info.data
    console.log($scope.user_info.user_id)

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    if ($scope.user_info.role == 'admin' || $scope.user_info.role == 'assigner') {
        $scope.admin_or_assigner_can_see = true
    }



    //logout funtion 
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function 

    $scope.clear_filter = function () {
        $scope.tableSearch_by_status_id = ''
        $scope.tableSearch_by_developer_id = ''
    }

    $scope.get_all_users = function () {
        // console.log('Department_id = ' + department_id) 
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllUsers.php'

        }).then(function successCallback(response) {

            $scope.all_users = response.data;
            console.log($scope.all_users)

        }, function errorCallback(response) {
            Swal.fire({
                type: 'warning',
                title: 'Network Connection Erro',
                text: 'Users Could not loaded'
            })

        });
    }
    $scope.get_all_users()


    $scope.statistics_description_name = 'Tasks'

    $scope.get_tasks_by_is_approved = function (user_id, is_approved, statistics_description_name, _user_info_role, department_id) {
        console.log('by approved: ' + _user_info_role)
        console.log('am here');
        console.log('user id: '+user_id)
        // console.log(status_id)
        $scope.statistics_description_name = statistics_description_name

        if (_user_info_role == 'developer') {
            var _url = myConfig.url + '/getAllTasks_ForDeveloper.php?user_id=' + user_id + '&is_approved=' + is_approved
        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = myConfig.url + '/getAllTasks_ForAssigner_&_Admin.php?is_approved=' + is_approved
        } else if (_user_info_role == 'department_head') {
            var _url = myConfig.url + '/getAllTasks_ForDepartmentHead.php?is_approved=' + is_approved + '&department_id=' + department_id + '&user_id=' + user_id
        }
        $http({
            method: 'GET',
            url: _url

        }).then(function successCallback(response) {

            $scope.tasks = response.data['data'];
            // console.log($scope.task)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }


    $scope.get_tasks_by_status_id = function (user_id, status_id, statistics_description_name, _user_info_role, department_id) {
        console.log('by status id: ' + _user_info_role)
        console.log('user id: ' + user_id)
        // console.log(status_id)
        $scope.statistics_description_name = statistics_description_name


        if (_user_info_role == 'developer') {

            var _url = myConfig.url + '/getAllTasks_ForDeveloper.php?user_id=' + user_id + '&status_id=' + status_id

        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = myConfig.url + '/getAllTasks_ForAssigner_&_Admin.php?status_id=' + status_id
        } else if (_user_info_role == 'department_head') {
            var _url = myConfig.url + '/getAllTasks_ForDepartmentHead.php?status_id=' + status_id + '&department_id=' + department_id + '&user_id' + user_id
        }

        $http({
            method: 'GET',
            url: _url

        }).then(function successCallback(response) {

            $scope.tasks = response.data['data'];
            // console.log($scope.task)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }

    $scope.get_total_tasks = function (user_id, statistics_description_name, _user_info_role, department_id) {
        console.log('total task: ' + _user_info_role)
        // console.log(user_id)
        // console.log(status_id)

        $scope.statistics_description_name = statistics_description_name

        if (_user_info_role == 'developer') {

            var _url = myConfig.url + '/getAllTasks_ForDeveloper.php?user_id=' + user_id

        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = myConfig.url + '/getAllTasks_ForAssigner_&_Admin.php'
        } else if (_user_info_role == 'department_head') {
            var _url = myConfig.url + '/getAllTasks_ForDepartmentHead.php?department_id=' + department_id
        }

        $http({
            method: 'GET',
            url: _url

        }).then(function successCallback(response) {

            $scope.tasks = response.data['data'];
            // console.log($scope.task)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }


    // $scope.get_all_task = function () {
    //     $http({ 
    //         method: 'GET',
    //         url: myConfig.url + '/getAllTasks.php'

    //     }).then(function successCallback(response) {

    //         $scope.tasks = response.data['data'];
    //         console.log($scope.task)

    //     }, function errorCallback(response) {

    //         // alert("Error. Try Again!");

    //     });
    // }

    // $scope.get_all_task()



    // $scope.get_status = function (init) {
    //     $http({
    //         method: 'GET',
    //         url: myConfig.url + '/getCodeStatusCount.php?init=' + init 

    //     }).then(function successCallback(response) {

    //         $scope.approved_project = response.data[0].approved_project;
    //         $scope.unapproved_project = response.data[0].unapproved_project;
    //         $scope.status_stats = response.data[0].code_desc;
    //         console.log($scope.status_stats)
    //         // console.log($scope.approved_project)
    //         // console.log($scope.unapproved_project)

    //     }, function errorCallback(response) {

    //         // alert("Error. Try Again!");

    //     });
    // }
    // $scope.get_status('sta')



    $scope.get_statics_developer_for_developer = function (init, department_id, is_dept_head, user_id) {
        console.log(init)
        console.log(department_id)
        console.log(is_dept_head)
        console.log(user_id)


        var _url = myConfig.url + '/getCodeStatusCountTasks_ForDeveloper.php?init=' + init + '&department_id=' + department_id + '&is_dept_head=' + is_dept_head + '&user_id=' + user_id

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_tasks = response.data[0].total_tasks;
            $scope.approved_project = response.data[0].approved_project;
            $scope.unapproved_project = response.data[0].unapproved_project;
            $scope.status_stats = response.data[0].code_desc;
            // console.log($scope.status_stats)
            // console.log($scope.approved_project)
            // console.log($scope.unapproved_project)
            /**
             * ! to determine where the card id developer or admin or assigner
             */
            $scope._user_info_role = 'developer'

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });


    }
    // $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, $scope.user_info.is_dept_head, $scope.user_info.user_id, $scope.user_info.role)

    $scope.get_statics_developer_for_department_head = function (init, department_id) {
        console.log(init)
        console.log(department_id)


        var _url = myConfig.url + '/getCodeStatusCount_ForDepartmentHead.php?init=' + init + '&department_id=' + department_id

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_tasks = response.data[0].total_tasks;
            $scope.approved_project = response.data[0].approved_project;
            $scope.unapproved_project = response.data[0].unapproved_project;
            $scope.status_stats = response.data[0].code_desc;
            // console.log($scope.status_stats)
            // console.log($scope.approved_project)
            // console.log($scope.unapproved_project)
            /**
             * ! to determine where the card id developer or admin or assigner
             */
            $scope._user_info_role = 'department_head'

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });


    }
    // $scope.get_statics_developer_for_department_head('sta', $scope.user_info.department_id)



    $scope.get_statics_developer_for_assigner_and_admin = function (init) {

        var _url = myConfig.url + '/getCodeStatusCount_For_Assigner_&_Admin.php?init=' + init

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_tasks = response.data[0].total_tasks;
            $scope.approved_project = response.data[0].approved_project;
            $scope.unapproved_project = response.data[0].unapproved_project;
            $scope.status_stats = response.data[0].code_desc;
            // console.log($scope.status_stats)
            // console.log($scope.approved_project)
            // console.log($scope.unapproved_project)
            /**
             * ! to determine where the card id developer or admin or assigner
             */
            $scope._user_info_role = 'assigner_and_admin'

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });


    }

    // $scope.get_statics_developer_for_assigner_and_admin('sta')

    // if (($scope.user_info.role == 'assigner' || $scope.user_info.role == 'admin ') && ($scope.user_info.is_dept_head == 1 || $scope.user_info.is_dept_head == 0)) {

    if (($scope.user_info.role == 'admin' || $scope.user_info.role == 'assigner' || $scope.user_info.role == 'guest') && ($scope.user_info.is_dept_head == 1 || $scope.user_info.is_dept_head == 0)) {
        $scope.get_statics_developer_for_assigner_and_admin('sta')
        $scope.get_total_tasks($scope.user_info.user_id, 'total tasks', 'assigner_and_admin', $scope.user_info.department_id)

        // $scope.get_tasks_by_is_approved($scope.user_id, $scope.is_approved, 'total', 'assigner_and_admin', $scope.department_id)z
    }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 1) {
        $scope.get_statics_developer_for_department_head('sta', $scope.user_info.department_id)
        $scope.get_total_tasks($scope.user_info.user_id, 'total tasks', 'department_head', $scope.user_info.department_id)
    }
    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0 && $scope.user_info.role == 'assigner') {
    //     $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
    // }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0) {
        $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
        $scope.get_total_tasks($scope.user_info.user_id, 'total tasks', 'developer', $scope.user_info.department_id)
    }





});