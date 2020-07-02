sheetApp.controller('EditTaskCtrl', function ($scope, $http, check_auth, myConfig, $timeout, $location, $localStorage) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data
    $scope.user_id = $localStorage.user_info.data['user_id']

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    $scope.url_project_id = $location.search().project_id
    $scope.task_id = $location.search().task_id
    $scope.url_project_name = $location.search().project_name
    $scope.url_department_id = $location.search().department_id
    $scope.p_start_date = $location.search().start_date
    $scope.p_end_date = $location.search().end_date
    $scope.p_start_date__ = Date.parse($location.search().start_date)
    $scope.p_end_date__ = Date.parse($location.search().end_date)

    console.log($scope.url_project_id)
    console.log($scope.url_project_name)
    console.log($scope.url_department_id)


    if (!$location.search().project_id || !$location.search().project_name || !$location.search().department_id) {
        $location.path('/404')
    }


    //logout funtion 
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function 


    $scope.get_all_project = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllProjects.php'

        }).then(function successCallback(response) {

            $scope.projects = response.data['data'];
            console.log($scope.projects)

        }, function errorCallback(response) {

            Swal.fire({
                type: 'warning',
                title: 'Network Connection Erro',
                text: 'Projects Could not loaded'
            })

        });
    }
    $scope.get_all_project()


    $scope.get_all_clients = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllClientse.php'

        }).then(function successCallback(response) {

            $scope.all_clients = response.data;
            console.log($scope.all_clients)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");
            Swal.fire({
                type: 'warning',
                title: 'Network Connection Erro',
                text: 'Clients Could not loaded'
            })

        });
    }
    $scope.get_all_clients()


    $scope.get_code_desc = function (init) {
        $http({
            method: 'GET',
            url: myConfig.url + '/getCodeDescription.php?init=' + init

        }).then(function successCallback(response) {

            $scope.code_desc = response.data['0'].code_desc;
            console.log($scope.code_desc)

        }, function errorCallback(response) {

            Swal.fire({
                type: 'warning',
                title: 'Network Connection Erro',
                text: 'Priority Could not loaded'
            })

        });
    }
    $scope.get_code_desc('pri')

    // $scope.get_departments = function (dpt) {
    //     $http({
    //         method: 'GET',
    //         url: myConfig.url + '/getCodeDescription.php?init=' + dpt

    //     }).then(function successCallback(response) {

    //         $scope.departments = response.data[0].code_desc;
    //         console.log($scope.departments)

    //     }, function errorCallback(response) {

    //         alert("Error. Try Again!");

    //     });
    // }
    // $scope.get_departments('dpt') 

    $scope.get_all_users = function (department_id) {
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
    $scope.get_all_users($scope.url_department_id)

    $scope.get_one_task = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getOnetask.php?task_id=' + $scope.task_id

        }).then(function successCallback(response) {


            $scope.oneTask = response.data['data'][0]
            $scope.task_start_date = new Date($scope.oneTask.task_start_date).toISOString().substring(0, 10)
            $scope.task_end_date = new Date($scope.oneTask.task_end_date).toISOString().substring(0, 10)
            console.log($scope.oneTask)
            // console.log($scope.oneTask.task_start_date)
            // console.log($scope.oneTask.task_start_date)
        })
    }

    $scope.get_one_task()


    $scope.updateTask = function (project_id_, priority_, task_, developer_,
        client_id_, start_date_, end_date_) {
        // console.log(project_id_) 
        // console.log(priority_)
        // console.log(task_)
        // console.log(developer_) 
        // console.log(client_id_)
        console.log(start_date_)
        console.log(end_date_)
        var p_start_date = Date.parse($scope.p_start_date)
        var p_end_date = Date.parse($scope.p_end_date)

        var t_start_date = $('#start_date_').val()
        var t_end_date = $('#end_date_').val()


        var data = {
            user_id: $scope.user_info.user_id,
            project_id: project_id_.trim(),
            task_id: $scope.task_id,
            priority: priority_.trim(),
            task_name: $('#task_').val(),
            client_id: client_id_.trim(),
            assigned_to: developer_.trim(),
            assigned_by: $scope.user_id.trim(),
            t_start_date: new Date(t_start_date).toISOString().substring(0, 10),
            t_end_date: new Date(t_end_date).toISOString().substring(0, 10),
            p_start_date: $scope.p_start_date,
            p_end_date: $scope.p_end_date
        }
        console.log(data);

        var sendRequest = function () {

            // $http POST function
            $http({

                method: 'POST',
                url: myConfig.url + '/updateTask.php',
                data: data

            }).then(function successCallback(response) {
                    $res = response.data
                    console.log($res.status)

                    if ($res.status == 'success') {

                        Swal.fire({
                            type: 'success',
                            title: 'REFJ-0000' + $res.task_id,
                            text: $res.message
                        })


                        //    Swal.fire($res.status, "success"); 
                        setTimeout(() => {
                            // $scope.get_all_users()
                            $('#add_task_form')[0].reset()
                            $('#customRadio4').val('')
                            $('#customRadio6').val('')
                            $timeout(window.location = 'task_detail/' + $scope.task_id, 2000);
                            // $timeout($location.path('/project/' + $scope.url_project_id), 2000);
                        }, 2000);
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: $res.status,
                            text: $res.message,
                        })

                        // Swal.fire($res.message);
                        // setTimeout(() => {
                        //     // $('#client_message_error').hide();
                        // }, 4000);
                    }

                },
                function errorCallback(response) {
                    // $res = response.data
                    // console.log($res)
                    Swal.fire({
                        type: 'warning',
                        title: 'Sorry',
                        text: 'Something went wrong',
                    })

                })


        }

        if (p_start_date > start_date_) {
            Swal.fire({
                type: 'error',
                title: 'Invalid Date',
                text: 'Task start date can not be earlier than Project start date',
            })
        } else if (p_end_date < start_date_) {
            Swal.fire({
                type: 'error',
                title: 'Invalid Date',
                text: 'Task end date can not be earlier than Project start date',
            })
        } else if (p_end_date < end_date_) {
            Swal.fire({
                type: 'error',
                title: 'Invalid Date',
                text: 'Task end date can not go beyond than Project end date',
            })
        } else if (p_start_date > end_date_) {
            Swal.fire({
                type: 'error',
                title: 'Invalid Date',
                text: 'Task start date can not go beyond than Project end date',
            })
        } else {
            sendRequest()
        }

    }


});