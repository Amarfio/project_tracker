sheetApp.controller('DashboardCtrl', function ($scope, dataService, check_auth, myConfig, $http, $location, $localStorage, $location) {
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

    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == '0') {
        return $location.path('/dashboard_t')
    }

    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == '1') {
    //     return $location.path('/dashboard_t')
    // }

    //logout funtion 
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function  

    $scope.statistics_description_name = 'All'

    $scope.get_projects_by_is_approved = function (user_id, is_approved, statistics_description_name, _user_info_role, department_id) {
        // console.log(' is+approved: ' + is_approved)
        // console.log(user_id)
        console.log(statistics_description_name)
        $scope.statistics_description_name = statistics_description_name

        if (_user_info_role == 'developer') {
            var _url = 'getAllProjects_ForDeveloper.php?user_id=' + user_id + '&is_approved=' + is_approved + '&department_id=' + department_id
        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = 'getAllProjects_ForAssigner_&_Admin.php?is_approved=' + is_approved
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
    $scope.downloadExcelSheet = function (){
        var _url = 'projectReportByDeptId.php?dept_id='+dept_ID;
        console.log("inside the download excel method "+dept_ID);
        // return false;

        $http({

            method: 'GET',
            url: myConfig.url + '/projectReportByDeptId.php?dept_id=' + dept_ID

        }).then(function successCallback(response) {
            console.log("it is done");
            console.log(response);
            // var $res = response.data;
            // console.log($res.status)
            // console.log(res);
            // return false;


            // if ($res.status == 'success') {

            //     Swal.fire({
            //         type: 'success',
            //         title: 'PROJ-0000' + $res.project_id,
            //         text: $res.message
            //     })
            //     // //    Swal.fire($res.status, "success");

            //     $('#add_project_form')[0].reset()

            //     $timeout(
            //         window.location = 'add_task?project_id=' + $res.project_id + '&project_name=' + project_name_ + '&department_id=' + department_id_ + '&start_date=' + start_date_ + '&end_date=' + end_date_

            //         // $location.path('/add_task?project_id=' + $res.project_id + '&project_name=' + project_name_ + '&department_id=' + department_id_)

            //         , 1000);

            //     // $timeout(
            //     //     window.location = 'add_task?project_id=' + $res.project_id + '&project_name=' + project_name_ + '&department_id=' + department_id_ + '&start_date=' + $res.start_date + '&end_date=' + $res.end_date

            //     //     // $location.path('/add_task?project_id=' + $res.project_id + '&project_name=' + project_name_ + '&department_id=' + department_id_)

            //     //     , 2000);


            // } else {
            //     Swal.fire({
            //         type: 'error',
            //         title: $res.status,
            //         text: $res.message,
            //     })

            // }




        }, function errorCallback(response) {

            Swal.fire({
                type: 'warning',
                title: 'Sorry',
                text: 'Something went wrong',
            })

        });
        // dataService.downloadSheet(_url)
        //     .then(function successCallback(response) {

        //         console.log("it is done download completed. ");
        //         // $scope.projects = response.data['data'];
        //         // console.log($scope.projects)

        //     }, function errorCallback(response) {

        //         // alert("Error. Try Again!");

        //     });
            return false;
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
        console.log('by status id: ' + _user_info_role)
        console.log(user_id)
        console.log(statistics_description_name)
        console.log(status_id)
        $scope.statistics_description_name = statistics_description_name


        if (_user_info_role == 'developer') {

            var _url = myConfig.url + '/getAllProjects_ForDeveloper.php?user_id=' + user_id + '&status_id=' + status_id

        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = myConfig.url + '/getAllProjects_ForAssigner_&_Admin.php?status_id=' + status_id
        } else if (_user_info_role == 'department_head') {
            var _url = myConfig.url + '/getAllProjects_ForDepartmentHead.php?status_id=' + status_id + '&department_id=' + department_id
        }

        $http({
            method: 'GET',
            url: _url

        }).then(function successCallback(response) {

            $scope.projects = response.data['data'];
            // console.log($scope.projects)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
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


        var _url = myConfig.url + '/getCodeStatusCount_ForDeveloper.php?init=' + init + '&department_id=' + department_id + '&is_dept_head=' + is_dept_head + '&user_id=' + user_id

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
            $scope._user_info_role = 'developer'

        }, function errorCallback(response) {

            // alert("Error. Try Again!"); 

        });


    }
    // $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, $scope.user_info.is_dept_head, $scope.user_info.user_id, $scope.user_info.role)

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

        var _url = myConfig.url + '/getCodeStatusCount_Project_For_Assigner_&_Admin_1.php?init=' + init

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_projects = response.data[0].total_projects;
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
        $scope.get_statics_developer_for_assigner_and_admin('psta')
        // $scope.get_total_projects()
        $scope.get_total_projects($scope.user_info.user_id, 'total', 'assigner_and_admin', $scope.user_info.department_id)
    }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 1) {
        $scope.get_statics_developer_for_department_head('psta', $scope.user_info.department_id, 1)
        $scope.get_total_projects($scope.user_info.user_id, 'total', 'department_head', $scope.user_info.department_id)
    }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0) {
        $scope.get_statics_developer_for_developer('psta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
        $scope.get_total_projects($scope.user_info.user_id, 'total', 'developer', $scope.user_info.department_id)
    }





});