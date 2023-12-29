sheetApp.controller('ProjectUpdatesDashboardCtrl', function ($scope, dataService, check_auth, myConfig, $http, $location, $localStorage, $location) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data
    $scope.user_role = $scope.user_info.role
    var user_role = $localStorage.user_info.data
    console.log($scope.user_info.user_id)
    console.log($scope.user_info.department_id)

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    $scope.money = 'hello';

    //get all users dashboard details
    $scope.get_all_users = function () {
        // console.log('Department_id = ' + department_id) 
        let dept_id = $scope.user_info.department_id;
        console.log()
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


    if ($scope.user_info.role == 'admin' || $scope.user_info.role == 'assigner') {
        $scope.admin_or_assigner_can_see = true
    }

    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == '0') {
    //     return $location.path('/dashboard_t')
    // }

    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == '1') {
    //     return $location.path('/dashboard_t')
    // }

    //logout funtion 
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function  

    $scope.statistics_description_name = 'Project with Updates'

    $scope.get_projects_by_is_approved = function (user_id, is_approved, statistics_description_name, _user_info_role, department_id) {
        // console.log(' is+approved: ' + is_approved)
        console.log(user_id)
        console.log(_user_info_role)
        console.log(department_id)
        console.log(statistics_description_name)
        $scope.statistics_description_name = statistics_description_name

        if (_user_info_role == 'developer') {
            var _url = 'getAllProjects_ForDeveloper.php?user_id=' + user_id + '&is_approved=' + is_approved + '&department_id=' + department_id
        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = 'getAllProjectsUpdates_ForAssigner_&_Admin.php?is_approved=' + is_approved
            console.log(_url);
        } else if (_user_info_role == 'department_head') {
            var _url = 'getAllProjects_ForDepartmentHead.php?is_approved=' + is_approved + '&department_id=' + department_id
        }

        dataService.get_all_project(_url)
            .then(function successCallback(response) {
                $scope.projects = response.data['data'];
                console.log($scope.projects)

            }, function errorCallback(response) {

                // alert("Error. Try Again!");

            });


        // $http({
        //     method: 'GET',
        //     url: _url

        // }).then(function successCallback(response) {

        //     $scope.tasks = response.data['data'];
        //     console.log($scope.task)

        // }, function errorCallback(response) {

        //     // alert("Error. Try Again!");

        // });
    }



    $scope.clear_filter = function () {
        $scope.tableSearch_by_department_id = ''
        $scope.tableSearch_by_status_id = ''
    }

     dept_ID = $scope.user_info.department_id;
    $scope.downloadExcel = function (){
        console.log("am here now");
        // window.location.href = './apiSheet/projectReportByDeptId.php';
        window.location.href = './apiSheet/projectReportById2.php?dept_id='+dept_ID;
        // window.location.href = './apiSheet/projectReportById3.php';

        // window.location.href = './apiSheet/projectReport.php';
        console.log("inside the download excel method "+dept_ID);
        // return false;

        
    }

    // $scope.apply_filter = function (filter_department, filter_status) {
    //     console.log(filter_department)
    //     console.log(filter_status)

    //     if ((filter_department == undefined) && (filter_status == undefined)) {
    //         Swal.fire({
    //             type: "error",
    //             title: "Can\'t Apply Empty filter"
    //         });

    //         console.log('-------------------------------')
    //         console.log('Both dept and status is not defined ')
    //         console.log('-------------------------------')

    //     }

    //     if ((filter_department == undefined) && (filter_status !== undefined)) {
    //         var route = 'getProjectsByFilter.php?status_id=' + filter_status
    //         console.log(route)
    //         get_projects(route)

    //         console.log('-------------------------------')
    //         console.log('status is defined')
    //         console.log('-------------------------------')

    //     }

    //     if ((filter_status === undefined) && (filter_department !== undefined)) {
    //         var route = 'getProjectsByFilter.php?department_id=' + filter_department
    //         console.log(route)
    //         get_projects(route)

    //         console.log('-------------------------------')
    //         console.log('department is defined')
    //         console.log('-------------------------------')
    //     }

    //     if ((filter_department !== undefined) && (filter_status !== undefined)) {
    //         var route = 'getProjectsByFilter.php?department_id=' + filter_department + '&status_id=' + filter_status
    //         console.log(route)
    //         get_projects(route)

    //         console.log('-------------------------------')
    //         console.log('Both is defined')
    //         console.log('-------------------------------')
    //     }



    // // }

    // var get_projects = function (route) {

    //     dataService.get_all_project(route)
    //         .then(function successCallback(response) {
    //             $scope.projects = response.data['data'];
    //             console.log($scope.projects)

    //         }, function errorCallback(response) {

    //             // alert("Error. Try Again!");

    //         });

    // }



    $scope.get_departments = function (dpt) {
        $http({
            method: 'GET',
            url: myConfig.url + '/getCodeDescription.php?init=' + dpt

        }).then(function successCallback(response) {

            $scope.departments = response.data[0].code_desc;
            console.log($scope.departments)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }
    $scope.get_departments('dpt')



    $scope.get_tasks_by_status_id = function (user_id, status_id, statistics_description_name, _user_info_role, department_id) {
        // console.log('by status id: ' + _user_info_role)
        console.log(user_id)
        console.log('this is stats '+statistics_description_name)
        console.log('the new status id ' + status_id)
        $scope.statistics_description_name = statistics_description_name


        if (_user_info_role == 'developer') {
            var _url = myConfig.url + '/getAllProjects_ForDeveloper.php?user_id=' + user_id + '&status_id=' + status_id
        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = myConfig.url + '/getAllProjectsUpdates_ForAssigner_&_Admin.php?status_id=' + status_id
        } else if (_user_info_role == 'department_head') {
            var _url = myConfig.url + '/getAllProjects_ForDepartmentHead.php?status_id=' + status_id + '&department_id=' + department_id
        }

        $http({
            method: 'GET',
            url: _url

        }).then(function successCallback(response) {

            $scope.projects = response.data['data'];
            console.log($scope.projects);
            $scope.project_avg = response.data['data'].completion;

            // $scope.project_avg = $scope.projects.completion;

            if ($scope.project_avg >= 80) {
                $scope.percentage_color = 'success'
            } else if ($scope.project_avg >= 60) {
                $scope.percentage_color = 'info'
            } else if ($scope.project_avg >= 40) {
                $scope.percentage_color = 'primary'
            } else if ($scope.project_avg >= 20) {
                $scope.percentage_color = 'warning'
            }
            //  else {
            //     $scope.percentage_color = 'danger'
            // }

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }

    $scope.getDownloadProjectUpdates = function(){
        console.log("download to take place here");

        // window.location.href = './apiSheet/projectReportById2.php';
        //go to this location and download the project updates
        $scope.status_id = 6;
        // window.location.href = './apiSheet/projectUpdateExcelDownload.php?status_id='+ $scope.status_id;
    }

    // $scope.get_all_project = function () {
    //     dataService.get_all_project('getAllProjects.php')
    //         .then(function successCallback(response) {
    //             $scope.projects = response.data['data'];
    //             console.log($scope.projects)

    //         }, function errorCallback(response) {

    //             // alert("Error. Try Again!");

    //         });
    // }
    // $scope.get_all_project()


    $scope.get_total_projects = function (user_id, statistics_description_name, _user_info_role, department_id) {
        console.log('total task: ' + _user_info_role)
        // console.log(user_id)
        // console.log(status_id)

        $scope.statistics_description_name = statistics_description_name

        if (_user_info_role == 'developer') {

            var _url = 'getAllProjects_ForDeveloper.php?user_id=' + user_id

        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = 'getAllProjects_ForAssigner_&_Admin.php'
        } else if (_user_info_role == 'department_head') {
            var _url = 'getAllProjects_ForDepartmentHead.php?department_id=' + department_id
        }

        dataService.get_all_project(_url)
            .then(function successCallback(response) {
                $scope.projects = response.data['data'];
                console.log($scope.projects)

            }, function errorCallback(response) {

                // alert("Error. Try Again!");

            });

        // $http({
        //     method: 'GET',
        //     url: _url

        // }).then(function successCallback(response) {

        //     $scope.tasks = response.data['data'];
        //     // console.log($scope.task)

        // }, function errorCallback(response) {

        //     // alert("Error. Try Again!");

        // });
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

        // return false;
        var _url = myConfig.url + '/getCodeStatusCount_ForDeveloper.php?init=' + init + '&department_id=' + department_id + '&is_dept_head=' + is_dept_head + '&user_id=' + user_id
        // var _url = 'getAllProjects_ForDeveloper.php?user_id=' + user_id 
        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_projects = response.data[0].total_projects;
            $scope.status_of_project = response.data[0].code_desc;
            // $scope.status_stats = response.data[0].code_desc;
            console.log(response.data)
            /**
             * ! to determine where the card id developer or admin or assigner
             */
            // $scope._user_info_role = 'developer'

        }, function errorCallback(response) {

            // alert("Error. Try Again!"); 

        });


    }
    // $scope.get_statics_developer_for_developer('psta', $scope.user_info.department_id, $scope.user_info.is_dept_head, $scope.user_info.user_id, $scope.user_info.role)

    $scope.get_statics_developer_for_department_head = function (init, department_id) {
        console.log(init)
        console.log(department_id)


        var _url = myConfig.url + '/getCodeStatusCount_Project_For_Dept_Head.php?init=' + init + '&department_id=' + department_id

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_projects = response.data[0].total_projects;
            $scope.status_of_project = response.data[0].code_desc;
            // $scope.status_stats = response.data[0].code_desc;
            console.log(response.data)
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

        var _url = myConfig.url + '/getCodeStatusCount_Project_Updates_For_Assigner_&_Admin_1.php?init=' + init

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_projects = response.data[0].total_projects;
            console.log(" here we dey " + $scope.total_projects);
            $scope.status_of_project = response.data[0].code_desc;
            // $scope.status_stats = response.data[0].code_desc;
            console.log(response.data)
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

    // if (($scope.user_info.role == 'admin' || $scope.user_info.role == 'assigner') && ($scope.user_info.is_dept_head == 1 || $scope.user_info.is_dept_head == 0)) {
    //     $scope.get_statics_developer_for_assigner_and_admin('psta')
    //     // $scope.get_total_projects()
    // }
    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 1) {
    //     $scope.get_statics_developer_for_department_head('sta', $scope.user_info.department_id)
    // }
    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0) {
    //     $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
    // }


    if (($scope.user_info.role == 'admin' || $scope.user_info.role == 'assigner' || $scope.user_info.role == 'guest') && ($scope.user_info.is_dept_head == 1 || $scope.user_info.is_dept_head == 0)) {
        $scope.get_statics_developer_for_assigner_and_admin('PUS')
        $scope.get_tasks_by_status_id($scope.user_info.user_id, 120, 'Project with Updates', 'assigner_and_admin', $scope.user_info.department_id);
        $scope.get_statics_developer_for_developer('PUS', $scope.user_info.department_id, 0, $scope.user_info.user_id)
        // $scope.get_total_projects($scope.user_info.user_id, 'total', 'assigner_and_admin', $scope.user_info.department_id)
        
    }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 1) {
        $scope.get_statics_developer_for_department_head('PUS', $scope.user_info.department_id, 1)
        // $scope.get_total_projects($scope.user_info.user_id, 'total', 'department_head', $scope.user_info.department_id)
    }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0) {
        $scope.get_statics_developer_for_developer('PUS', $scope.user_info.department_id, 0, $scope.user_info.user_id)
        // $scope.get_total_projects($scope.user_info.user_id, 'total', 'developer', $scope.user_info.department_id)
    }





});