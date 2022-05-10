sheetApp.controller('TaskCtrl', function ($scope, $http, $routeParams, check_auth, myConfig, $location, $localStorage) {

    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)
    $scope.myConfig_file_url = myConfig.file_url


    //logout funtion 
    $scope.logout = function () {
        check_auth.logout()
    }
    // end logout function 


    $scope.get_project_status = function (init) {
        $http({
            method: 'GET',
            url: myConfig.url + '/getProjectStatus.php?init=' + init

        }).then(function successCallback(response) {

            $scope.project_status_list = response.data;
            console.log($scope.project_status_list)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }
    $scope.get_project_status('psta')




    console.log($scope.project_id = $routeParams.project_id)

    $scope.get_one_project = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getOneProject.php?project_id=' + $scope.project_id

        }).then(function successCallback(response) {

            if (!response.data['data']) {
                return $location.path('/404')
            }

            $scope.project_avg = response.data['project_avg_percentage'];
            $scope.project = response.data['data'][0];
            $scope.getStatus = response.data['data'][0].project_status
            // $scope.department = response.data['data'][0].department
            console.log($scope.project.department_id)
            console.log($scope.project)
            console.log($scope.getStatus)

            if ($scope.project_avg >= 80) {
                $scope.percentage_color = 'success'
            } else if ($scope.project_avg >= 60) {
                $scope.percentage_color = 'info'
            } else if ($scope.project_avg >= 40) {
                $scope.percentage_color = 'primary'
            } else if ($scope.project_avg >= 20) {
                $scope.percentage_color = 'warning'
            } else {
                $scope.percentage_color = 'danger'
            }

            // if ($scope.user_info.is_dept_head == '1' && $scope.user_info.department_id == $scope.project.department_id && $scope.project.is_approved == '0') {
            //     $scope.approve_btn = true
            // }


        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });



    }
    $scope.get_one_project()

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


    // $scope.approve_project = function (approved_by, project_id) {
    //     console.log(approved_by)
    //     console.log(project_id)

    //     $http({
    //         method: 'GET',
    //         url: myConfig.url + '/approve_project.php?approved_by=' + approved_by + '&project_id=' + project_id

    //     }).then(function successCallback(response) {
    //         var $res = response.data
    //         console.log(response.data)
    //         if ($res.status == 'success') {
    //             Swal.fire({
    //                 type: 'success',
    //                 title: 'PROJ-0000' + $res.project_id,
    //                 text: $res.message
    //             })

    //             $scope.get_one_project()
    //         }

    //     }, function errorCallback(response) {

    //         // alert("Error. Try Again!");

    //     });

    // }



    $scope.submit_for_approval = function () {
        $('.modal').modal('hide');

        Swal.queue([{
            title: 'Proccessing ...  ',
            // showLoaderOnConfirm: true,
            onBeforeOpen: () => {
                Swal.showLoading()
            },
            showLoaderOnConfirm: true,
        }])

        $http({
            method: 'GET',
            url: myConfig.url + '/submit_project_for_approval.php?project_id=' + $scope.project_id

        }).then(function successCallback(response) {
            var $res = response.data
            console.log(response.data)
            if ($res.status == 'success') {
                Swal.fire({
                    type: 'success',
                    title: 'PROJ-0000' + $res.project_id,
                    text: $res.message
                })

                $scope.get_one_project()
            } else if ($res.status == 'failed') {
                Swal.fire({
                    type: 'error',
                    title: $res.message

                })

                $scope.get_one_project()
            }

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });

    }



    // if ($scope.getStatus == 'drafts' && $scope.user_info.role == 'assigner') {
    //     $scope.submit_project_for_approval = true

    // }

    // if ($scope.user_info.can_approve == '1' && $scope.getStatus == 'unapproved') {
    //     $scope.approve_this_project = true
    // }

    $scope.reassign_project = function (department_id) {
        var comment = $('#comment_for_reassign').val();
        var department_id = department_id
        console.log(department_id);

        if (comment == undefined || comment == '' || department_id == undefined || department_id == '') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            })

            Toast.fire({
                type: 'error',
                title: 'Field can not be empty'
            })
        } else {
            $('.modal').modal('hide');
            Swal.queue([{
                title: 'Processing Approval ...  ',
                // showLoaderOnConfirm: true,
                onBeforeOpen: () => {
                    Swal.showLoading()
                },
                showLoaderOnConfirm: true,
            }])

            $http({
                method: 'GET',
                url: myConfig.url + '/reassign_project.php?project_id=' + $scope.project_id + '&reassignedBy=' + $scope.user_info.user_id + '&department_id=' + department_id + '&comment=' + comment + '&user_id=' + $scope.user_info.user_id

            }).then(function successCallback(response) {
                var $res = response.data
                console.log(response.data)
                if ($res.status == 'success') {
                    Swal.fire({
                        type: 'success',
                        title: 'PROJ-0000' + $res.project_id,
                        text: $res.message
                    })

                    $scope.get_one_project()
                }
                if ($res.status == 'failed') {
                    Swal.fire({
                        type: 'error',
                        title: 'PROJ-0000' + $res.project_id,
                        text: $res.message
                    })

                    $scope.get_one_project()
                }

            }, function errorCallback(response) {

                // alert("Error. Try Again!");

            });

        }

    }




    $scope.approve_project = function () {

        var comment = $('#comment_approve_or_reject').val();

        if (comment == undefined || comment == '') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            })

            Toast.fire({
                type: 'error',
                title: 'Comment can be empty'
            })
        } else {
            $('.modal').modal('hide');
            Swal.queue([{
                title: 'Processing Approval ...  ',
                // showLoaderOnConfirm: true,
                onBeforeOpen: () => {
                    Swal.showLoading()
                },
                showLoaderOnConfirm: true,
            }])

            $http({
                method: 'GET',
                url: myConfig.url + '/approve_project.php?project_id=' + $scope.project_id + '&approvedBy=' + $scope.user_info.user_id + '&department_id=' + $scope.project.department_id + '&comment=' + comment

            }).then(function successCallback(response) {
                var $res = response.data
                console.log(response.data)
                if ($res.status == 'success') {
                    Swal.fire({
                        type: 'success',
                        title: 'PROJ-0000' + $res.project_id,
                        text: $res.message
                    })

                    $scope.get_one_project()
                }
                if ($res.status == 'failed') {
                    Swal.fire({
                        type: 'error',
                        title: 'PROJ-0000' + $res.project_id,
                        text: $res.message
                    })

                    $scope.get_one_project()
                }

            }, function errorCallback(response) {

                // alert("Error. Try Again!");

            });

        }


    }


    $scope.reject_project = function () {

        var comment = $('#comment_approve_or_reject').val();

        if (comment == undefined || comment == '') {
            // Swal.fire({
            //     type: 'error',
            //     title: 'hey',
            //     text: 'Emptyp',
            //     toast: true
            // })
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            })

            Toast.fire({
                type: 'error',
                title: 'Comment can be empty'
            })

        } else {
            $('.modal').modal('hide');
            Swal.queue([{
                title: 'Send Comment ...  ',
                // showLoaderOnConfirm: true,
                onBeforeOpen: () => {
                    Swal.showLoading()
                },
                showLoaderOnConfirm: true,
            }])

            $http({
                method: 'GET',
                url: myConfig.url + '/reject_project.php?project_id=' + $scope.project_id + '&approvedBy=' + $scope.user_info.user_id + '&department_id=' + $scope.project.department_id + '&comment=' + comment

            }).then(function successCallback(response) {
                var $res = response.data
                console.log(response.data)
                if ($res.status == 'success') {
                    Swal.fire({
                        type: 'success',
                        title: $res.message

                    })

                    $scope.get_one_project()
                }
                if ($res.status == 'failed') {
                    Swal.fire({
                        type: 'error',
                        title: $res.message

                    })

                    $scope.get_one_project()
                }

            }, function errorCallback(response) {

                // alert("Error. Try Again!");

            });

        }

    }



    $scope.get_tasks_by_status_id = function (user_id, status_id, statistics_description_name, _user_info_role, department_id) {
        console.log('by status id: ' + _user_info_role)
        // console.log(user_id)
        // console.log(status_id)
        $scope.statistics_description_name = statistics_description_name


        if (_user_info_role == 'developer') {

            var _url = myConfig.url + '/getOneProjectTasks_ForDeveloper.php?user_id=' + user_id + '&status_id=' + status_id + '&project_id=' + $scope.project_id

        } else if (_user_info_role == 'assigner_and_admin') {
            var _url = myConfig.url + '/getOneProjectTasks_ForAssigner_&_Admin.php?status_id=' + status_id + '&project_id=' + $scope.project_id
        } else if (_user_info_role == 'department_head') {
            var _url = myConfig.url + '/getOneProjectTasks_ForDepartmentHead.php?status_id=' + status_id + '&department_id=' + department_id + '&project_id=' + $scope.project_id
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




    $scope.get_statics_developer_for_developer = function (init, department_id, is_dept_head, user_id) {
        console.log(init)
        console.log(department_id)
        console.log(is_dept_head)
        console.log(user_id)

        var _url = myConfig.url + '/getCodeStatusCountOneProject_ForDeveloper.php?init=' + init + '&department_id=' + department_id + '&is_dept_head=' + is_dept_head + '&user_id=' + user_id + '&project_id=' + $scope.project_id

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_tasks = response.data[0].total_tasks;
            $scope.approved_tasks = response.data[0].approved_tasks;
            $scope.unapproved_tasks = response.data[0].unapproved_tasks;
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


        var _url = myConfig.url + '/getCodeStatusCountOfOneProject_ForDepartmentHead.php?init=' + init + '&department_id=' + department_id + '&project_id=' + $scope.project_id

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

        var _url = myConfig.url + '/getCodeStatusCountOfOneProject_Project_For_Assigner_&_Admin.php?init=' + init + '&project_id=' + $scope.project_id

        $http({
            method: 'GET',
            url: _url
        }).then(function successCallback(response) {

            $scope.total_tasks = response.data[0].total_tasks;
            $scope.approved_tasks = response.data[0].approved_tasks;
            $scope.unapproved_tasks = response.data[0].unapproved_tasks;
            $scope.status_stats = response.data[0].code_desc;
            console.log(response.data)
            // console.log($scope.status_stats)
            // console.log($scope.approved_tasks)
            // console.log($scope.unapproved_tasks)
            /**
             * ! to determine where the card id developer or admin or assigner
             */
            $scope._user_info_role = 'assigner_and_admin'

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });


    }



    if (($scope.user_info.role == 'admin' || $scope.user_info.role == 'assigner') && ($scope.user_info.is_dept_head == 1 || $scope.user_info.is_dept_head == 0)) {
        $scope.get_statics_developer_for_assigner_and_admin('sta')
        // $scope.get_total_projects()
    }
    if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 1) {
        $scope.get_statics_developer_for_department_head('sta', $scope.user_info.department_id)
    }
    // // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0 && $scope.user_info.role == 'assigner') {
    // //     $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
    // // }
    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0) {
    //     $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
    // }

    // $scope.get_statics_developer_for_assigner_and_admin('sta')
    // $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, $scope.user_info.is_dept_head, $scope.user_info.user_id)



});