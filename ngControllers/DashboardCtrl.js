sheetApp.controller('DashboardCtrl', function ($scope, dataService, check_auth, myConfig, $http, $location, $localStorage, $location) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data
    $scope.user_role = $scope.user_info.role
    console.log($scope.user_role)
    // var user_role = $localStorage.user_info.data
    console.log($scope.user_info.user_id)
    console.log($scope.user_info.department_id)

    //description of the dashboard
    $scope.dashboard_description = 'Project';

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)

    $scope.myConfig_file_url = myConfig.file_url

    //for check if developer value has changed
    $scope.dev_changed = 0;

    $scope.money = 'hello';

    //code for it to display if user is admin or assigner
    if ($scope.user_info.role == 'admin' || $scope.user_info.role == 'assigner') {
        $scope.admin_or_assigner_can_see = true
    }

    //code to hide if the project have been approved....
    // if()

    //get all users dashboard details
    // $scope.get_all_users = function () {
    //     // console.log('Department_id = ' + department_id) 
    //     let dept_id = $scope.user_info.department_id;
    //     console.log()
    //     $http({
    //         method: 'GET',
    //         url: myConfig.url + '/getAllUsers.php'

    //     }).then(function successCallback(response) {

    //         $scope.all_users = response.data;
    //         console.log("user results: ")
    //         console.log($scope.all_users)

    //     }, function errorCallback(response) {
    //         Swal.fire({
    //             type: 'warning',
    //             title: 'Network Connection Erro',
    //             text: 'Users Could not loaded'
    //         })

    //     });
    // }
    // $scope.get_all_users()



    // $scope.get_my_issues()

    //get all developers dashboard details for admin
    $scope.get_all_developers = function () {
        // console.log('Department_id = ' + department_id) 
        //  dept_id = $scope.user_info.department_id;
        console.log("we are here again");
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllDevs.php'

        }).then(function successCallback(response) {

            $scope.all_developers = response.data;
            console.log("This is the results");
            console.log($scope.all_developers)

        }, function errorCallback(response) {
            Swal.fire({
                type: 'warning',
                title: 'Network Connection Erro',
                text: 'Users Could not loaded'
            })

        });
    }
    // $scope.get_all_developers()

    //get all developers in a department
    $scope.get_all_developers_dpt_head = function (dept_id){
        dept_id = $scope.user_info.department_id;
        console.log(dept_id);
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllDevs.php?department_id='+dept_id

        }).then(function successCallback(response) {

            $scope.all_developers = response.data;
            console.log("This is the result with specified department ")
            console.log($scope.all_developers)

        }, function errorCallback(response) {
            Swal.fire({
                type: 'warning',
                title: 'Network Connection Error',
                text: 'Users Could not loaded'
            })

        });
    }

    console.log("devs by department");
    // $scope.get_all_developers(106);

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
    $scope.downloadExcel = function (){

        console.log(dept_ID);
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

    //code to format date
    
    //Controller:
    $scope.myDate = new Date('2014-03-08T00:00:00');



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


    console.log("here i come")
    //code to get the project stauts for filter
    $scope.get_p_status = function (pstatus) {
        $http({
            method: 'GET',
            url: myConfig.url + '/getCodeDescription.php?init=' + pstatus

        }).then(function successCallback(response){
            $scope.pstatus = response.data[0].code_desc;
            console.log("here i come")
            console.log($scope.pstatus)
        }, function errorCallback(response) {

        });
    }
    $scope.get_p_status('psta')


    $scope.get_tasks_by_status_id = function (user_id, status_id, statistics_description_name, _user_info_role, department_id, dev_changed) {
        console.log('role: ' + _user_info_role)
        console.log(user_id)
        console.log(statistics_description_name)
        console.log(status_id)
        // $scope.dev_changed = dev_changed
        console.log("this is the dev value changed "+dev_changed);
        $scope.statistics_description_name = statistics_description_name
        var _url = '';
        //check if the developer value has been changed then set the user id for the developer
        // if (dev_changed == 1){
        //     var _url = myConfig.url + '/getAllProjects_ForDeveloper_.php?user_id=' + user_id + '&status_id=' + status_id
        // }

        //check if the developer have been select by the admin or the head of department
        if (dev_changed == 0) {
            if (_user_info_role == 'developer') {
                 _url = myConfig.url + '/getAllProjects_ForDeveloper_.php?user_id=' + user_id + '&status_id=' + status_id
            } 
            else if (_user_info_role == 'assigner_and_admin') {

                if(user_id == 0){
                    _url = myConfig.url + '/getAllProjects_ForDepartmentHead.php?status_id=' + status_id + '&department_id=' + department_id
                }
                 _url = myConfig.url + '/getAllProjects_ForAssigner_&_Admin.php?status_id=' + status_id
            } 
            else if (_user_info_role == 'department_head') {
                 _url = myConfig.url + '/getAllProjects_ForDepartmentHead.php?status_id=' + status_id + '&department_id=' + department_id
            }
            // if (_user_info_role =='admin') {
            //     //  _url = myConfig.url + '/getAllProjects_ForDeveloper.php?user_id=' + user_id + '&status_id=' + status_id
            //     _url = myConfig.url + '/getAllProjects_ForAssigner_&_Admin.php?status_id=' + status_id
            // }
        }else{
             _url = myConfig.url + '/getAllProjects_ForDeveloper_.php?user_id=' + user_id + '&status_id=' + status_id
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

    $scope.get_tasks_by_status_id_ = function (user_id, status_id, statistics_description_name, _user_info_role, _user_info_department_id) {
        console.log('by status id: ' + _user_info_role)
        console.log(user_id)
        console.log(statistics_description_name)
        console.log(status_id)
        $scope.statistics_description_name = statistics_description_name


        // if (_user_info_role == 'developer') {
            // var _url = myConfig.url + '/getAllProjects_ForDeveloper.php?user_id=' + user_id + '&status_id=' + status_id


        // } else if (_user_info_role == 'assigner_and_admin') {
        //     var _url = myConfig.url + '/getAllProjects_ForAssigner_&_Admin.php?status_id=' + status_id
        // } else if (_user_info_role == 'department_head') {
        //     var _url = myConfig.url + '/getAllProjects_ForDepartmentHead.php?status_id=' + status_id + '&department_id=' + department_id
        // }
        if (_user_info_role =='admin') {
            var _url = myConfig.url + '/getAllProjects_ForDeveloper.php?user_id=' + user_id + '&status_id=' + status_id
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

    $scope.get_total_projects_ = function(user_id){
        console.log("we are here");
        var _url = 'getAllProjects_ForDeveloper.php?user_id=' + user_id

        dataService.get_all_project(_url)
                .then(function successCallback(response) {
                    $scope.projects = response.data['data'];
                    console.log($scope.projects)

                }, function errorCallback(response) {

                    // alert("Error. Try Again!");

                });

    } 
    
    $scope.get_total_projects = function (user_id, statistics_description_name, _user_info_role, department_id, dev_changed) {
        console.log('total task: ' + _user_info_role)
        console.log(user_id)
        // console.log(status_id)

        $scope.statistics_description_name = statistics_description_name
        console.log('here this: '+ $scope.statistics_description_name);

        //code to declare url for data 
        var _url = '';
        if (dev_changed == 0) {

            if (_user_info_role == 'developer') {
                _url = 'getAllProjects_ForDeveloper_.php?user_id=' + user_id
            } else if (_user_info_role == 'assigner_and_admin') {
                _url = 'getAllProjects_ForAssigner_&_Admin.php'
            } else if (_user_info_role == 'department_head') {
                _url = 'getAllProjects_ForDepartmentHead.php?department_id=' + department_id
            }

        }else{
            //this code checks for user id to determine to pick from department or user project details
            if(user_id == 0){
                _url = 'getAllProjects_ForDepartmentHead.php?department_id=' + department_id
            }else{
                _url = 'getAllProjects_ForDeveloper_.php?user_id=' + user_id;
            }
             

        }
        
        
        dataService.get_all_project(_url)
            .then(function successCallback(response) {
                $scope.projects = response.data['data'];
                console.log($scope.projects)
                console.log("results printed");

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

    //
    $scope.get_statics_developer_for_developer_ = function (init, user_id) {
        console.log(init)
        // console.log(department_id)
        // console.log(is_dept_head)
        console.log(user_id)

        // return false;
        var _url = myConfig.url + '/getCodeStatusCount_ForDeveloper_.php?init=' + init + '&user_id=' + user_id
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
            $scope._user_info_role = 'developer'

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

        $scope.get_total_projects($scope.user_info.user_id, 'total', 'assigner_and_admin', $scope.user_info.department_id, $scope.dev_changed);

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
        $scope.get_all_developers()
        $scope.get_statics_developer_for_assigner_and_admin('psta')
        // $scope.get_statics_developer_for_developer('psta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
        $scope.get_total_projects($scope.user_info.user_id, 'total', 'assigner_and_admin', $scope.user_info.department_id, $scope.dev_changed)
    }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 1) {
        // $scope.get_all_developers($scope.user_info.department_id)
        $scope.get_all_developers_dpt_head($scope.user_info.department_id);
        $scope.get_statics_developer_for_department_head('psta', $scope.user_info.department_id, 1)
        $scope.get_total_projects($scope.user_info.user_id, 'total', 'department_head', $scope.user_info.department_id, $scope.dev_changed)
    }

    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0) {
        // $scope.get_all_developers($scope.user_info.department_id)
        $scope.get_statics_developer_for_developer('psta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
        $scope.get_total_projects($scope.user_info.user_id, 'total', 'developer', $scope.user_info.department_id, $scope.dev_changed)
    }


    //code for admin screen to check projects for developers
    $('#developer').change(function(){
        var value = $(this).val();
        // console.log(value);

        $scope.dashboard_description = $('#developer option:selected').text();

        $scope.get_statics_developer_for_developer_('psta', value);
        $scope.get_total_projects(value, 'total', 'developer', $scope.user_info.department_id, $scope.dev_changed);
        // $scope.get_all_projects_for_developer_('psta', value);
        

        // $scope.get_total_projects_(value);

        $scope.user_info.user_id = value;
        $scope.dev_changed = 1;


        // $scope.get_tasks_by_status_id_(value, );

    });

    //code for department head screen to check projects for developers
    $('#developer_s').change(function(){
        var value = $(this).val();
        console.log(value);

        if (value == 0){
            window.location.reload();
        }

        $scope.get_statics_developer_for_developer_('psta', value);
        $scope.get_total_projects(value, 'total', 'developer', $scope.user_info.department_id, $scope.dev_changed);
        // $scope.get_all_projects_for_developer_('psta', value);
        

        // $scope.get_total_projects_(value);

        $scope.user_info.user_id = value;
        $scope.dev_changed = 1;
        


        // $scope.get_tasks_by_status_id_(value, );

    });

    $('#s_developer').change(function(){
        var value = $(this).val();
        $scope.developer_s = value;
    });

    //code for when an empty option is selected dashboard
    $scope.get_default = function (){
        window.location.reload();
    }

    $('#start_date').change(function(){
        $scope.s_startDate = $('#start_date').val();
        console.log($scope.s_startDate);
    });

    $('#end_date').change(function(){
        $scope.e_endDate = $('#end_date').val();
        console.log($scope.e_endDate);
    });

    $('#statusS').change(function(){
        $scope.statusS = $('#statusS').val();
        console.log($scope.statusS);
    });

    //code to change the statistics by the selected options for start date, end date and status
    $scope.get_by_date_range_and_status = function () {
        start_date = $scope.s_startDate;
        end_date = $scope.e_endDate;
        status_id = $scope.statusS;
        console.log(status_id);
        
        // return false;
        user_id = $scope.developer_s;

        if(start_date == undefined){
            start_date = new Date(null);
            start_date = start_date.toISOString().split("T");
            start_date = start_date[0];
        }
        if(end_date == undefined){
            end_date = new Date();
            end_date = end_date.toISOString().split("T");
            end_date = end_date[0];
        }

        console.log(start_date);
        console.log(end_date);

        var _url = myConfig.url + '/getAllProjects_ForDeveloper_.php?start_date='+ start_date +'&end_date=' + end_date+ '&status_id=' + status_id+ '&user_id=' + user_id;

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            // $scope.total_projects = response.data[0].total_projects;
            // $scope.status_of_project = response.data[0].code_desc;
            $scope.projects = response.data['data'];
            // $scope.status_stats = response.data[0].code_desc;
            console.log(response.data)
            // console.log($scope.status_stats)
            // console.log($scope.approved_project)
            // console.log($scope.unapproved_project)
            /**
             * ! to determine where the card id developer or admin or assigner
             */
            // $scope._user_info_role = 'assigner_and_admin'

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });

    }

    //code for super admin
    //code for filtering by department thus when a particular department is selected
    $('#department_s').change(function(){

        $scope.dashboard_description = $('#department_s option:selected').text();
        var value = $(this).val();
        console.log(value);
        $scope.user_info.department_id = value;

        $scope.get_statics_developer_for_department_head('psta', value, 1)
        $scope.get_total_projects(0, 'total', 'department_head', value, $scope.dev_changed)

        $scope.dev_changed = 0;

    });


});