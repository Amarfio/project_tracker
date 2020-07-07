sheetApp.filter('myDateFormat', function myDateFormat($filter) {
    return function (text) {
        var tempdate = new Date(text.replace(/-/g, "/"));
        return $filter('date')(tempdate, "EEEE, MMMM d, y @ h:mma");
    }
})
sheetApp.filter('myDateShortFormat', function myDateFormat($filter) {
    return function (text) {
        var tempdate = new Date(text.replace(/-/g, "/"));
        return $filter('date')(tempdate, "EEEE, MMMM d");
    }
})

sheetApp.controller('TaskDetailCtrl', function ($scope, $http, $routeParams, check_auth, myConfig, $location, $localStorage) {
    // $scope.task_id = atob($routeParams.task_id);
    $scope.task_id = $routeParams.task_id;
    console.log($scope.task_id);
    $scope.myConfig_file_url = myConfig.file_url

    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    //logout funtion 
    $scope.logout = function () {
        check_auth.logout()
    }
    // end logout function

    $scope.display_approved_btn = false

    $scope.get_all_attachments = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getAttachFileOfTaskComment.php?task_id=' + $scope.task_id

        }).then(function successCallback(response) {
            $scope.all_attachment_file = response.data;
            console.log($scope.all_attachment_file)

        }, function errorCallback(response) {

            // alert("Error. Try Again!"); 

        });
    }
    $scope.get_all_attachments()

    $scope.get_one_task = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getOnetask.php?task_id=' + $scope.task_id

        }).then(function successCallback(response) {


            var oneTask = response.data['data'];

            if (!oneTask) {
                return $location.path('/404')
            }
            $scope.project = oneTask[0]
            $scope.oneTask = oneTask[0]

            // $scope.hide_when_status_is_not_started = false

            if ($scope.oneTask.status == 'not started') {
                $scope.hide_when_status_is_not_started = true
            }



            // $scope.attachment_files = $scope.oneTask['attachment_file']

            var p_start_date = Date.parse($scope.p_start_date)
            var p_end_date = Date.parse($scope.p_end_date)
            console.log($scope.oneTask)

            console.log($scope.project.task_start_date)
            console.log($scope.project.task_end_date)
            var task_start_date = new Date($scope.project.task_start_date)
            var task_end_date = new Date($scope.project.task_end_date)
            var today_date = new Date('yyyy-mm-dd')

            $scope.get_get_actual_no_days_in_milliseconds = task_end_date.getTime() - today_date.getTime() 
            $scope.number_of_actual_days_till_end = ($scope.get_get_actual_no_days_in_milliseconds) / (1000 * 60 * 60 * 24)

            $scope.get_total_task_date_in_milliseconds = task_end_date.getTime() - task_start_date.getTime()
            $scope.number_of_days_till_end = ($scope.get_total_task_date_in_milliseconds) / (1000 * 60 * 60 * 24)

            $scope.get_spent_task_date_in_milliseconds = today_date.getTime() - task_start_date.getTime()
            $scope.number_of_days_till_today = ($scope.get_spent_task_date_in_milliseconds) / (1000 * 60 * 60 * 24)


            if ($scope.number_of_days_till_today < 0) {

                $scope.progress_bar_percentage = 100
                $scope.progress_bar_percentage_color = 'danger'
                console.log('Days has not started yet')

            } else if ($scope.number_of_days_till_today > 0 && $scope.number_of_days_till_today < $scope.number_of_days_till_end) {

                $scope.progress_bar_percentage = ($scope.number_of_days_till_today / $scope.number_of_days_till_end) * 100

                if ($scope.progress_bar_percentage >= 90) {
                    $scope.progress_bar_percentage_color = 'info'
                } else if ($scope.progress_bar_percentage >= 60) {
                    $scope.progress_bar_percentage_color = 'info'
                } else if ($scope.progress_bar_percentage >= 40) {
                    $scope.progress_bar_percentage_color = 'info'
                } else if ($scope.progress_bar_percentage >= 0) {
                    $scope.progress_bar_percentage_color = 'info'
                } else {
                    $scope.progress_bar_percentage_color = 'info'
                }

                //    console.log($scope.progress_bar_percentage)
                console.log('progress bar percentage: ' + $scope.progress_bar_percentage)
                // console.log('$scope.number_of_days_till_today: ' + $scope.number_of_days_till_today)
                // console.log('$scope.number_of_days_till_end: ' + $scope.number_of_days_till_end)
            } else if ($scope.number_of_days_till_today > 0 && $scope.number_of_days_till_today > $scope.number_of_days_till_end) {

                $scope.progress_bar_percentage = 100
                $scope.progress_bar_percentage_color = 'danger'

                //    console.log($scope.progress_bar_percentage)
                console.log('It is pass due date dates')
            }


            // if ((get_spent_task_date_in_milliseconds > get_total_task_date_in_milliseconds) >) {
            //    console.log( $scope.progress_bar_percentage = 100)
            // }else{
            //     console.log($scope.progress_bar_percentage = ($scope.number_of_days_till_today / get_total_task_date_in_milliseconds) * 100 )

            // }

            console.log($scope.number_of_days_till_today)
            $scope.percentage_completion = $scope.oneTask.completion
            console.log($scope.percentage_completion);

            console.log($scope.percentage_completion)
            if ($scope.percentage_completion >= 100) {
                $scope.percentage_color = 'success'
            } else if ($scope.percentage_completion >= 60) {
                $scope.percentage_color = 'info'
            } else if ($scope.percentage_completion >= 40) {
                $scope.percentage_color = 'primary'
            } else if ($scope.percentage_completion >= 20) {
                $scope.percentage_color = 'warning'
            } else {
                $scope.percentage_color = 'danger'
            }


            // DEFINING PRIVILLEGES AND ACCESS
            if ($scope.user_info.is_dept_head == '1' && $scope.user_info.department_id == $scope.project.department_id) {
                $scope.department_head_can_see = true
            }
            if ($scope.user_info.user_id == $scope.project.assigned_to_id) {
                $scope.developer_can_see = true
            }


        }, function errorCallback(response) {

            // alert("Error. Try Again!"); 

        });
    }
    $scope.get_one_task()


    $scope.get_task_comments = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getTaskComment.php?task_id=' + $scope.task_id

        }).then(function successCallback(response) {

            $scope.comments = response.data

            console.log($scope.comments)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }
    $scope.get_task_comments()

    setInterval($scope.get_task_comments(), 100000)


    // ATTACHMENT ICON FUNCTION

    $scope.attachment_file = 'None';
    $scope.get_file_input = function () {
        // $("#upload_file").click(function () {
        //     $("#id_file_field").trigger('click');
        // });
        $('#attached-file').change(function () {
            var value = this.value;
            var fileName = typeof value == 'string' ? value.match(/[^\/\\]+$/)[0] : value[0]
            // console.log(fileName);
            $scope.attachment_file = fileName
            $('#show_attach_name').text(fileName);
        })

    }
    // END ATTACHMENT ICON FUNCTION



    $scope.upload = function () {
        var file = $scope.uploadfile;
        var fd = new FormData();
        var files = document.getElementById('attached-file').files[0];
        fd.append('file', files);

        $http({
            method: 'post',
            url: myConfig.url + '/uploadFile.php',
            data: fd,
            headers: {
                'Content-Type': undefined
            },
        }).then(function successCallback(response) {
            // Store response data
            $scope.response = response.data;
            console.log($scope.response)
            $('#show_attach_name').text('None');
            $scope.attachment_file = 'None'
        });
    }



    $scope.createComment = function (comment_message, data) {
        if (comment_message == '' || comment_message == undefined) {

        } else {
            //  console.log(data);
            //$http POST function 

            $scope.upload()

            $http({

                method: 'POST',
                url: myConfig.url + '/createComment.php',
                data: data

            }).then(function successCallback(response) {
                $res = response.data
                console.log($res);
                if ($res.status == 'success') {
                    $('#comment_input_form')[0].reset()

                    $scope.get_task_comments()
                    $scope.get_all_attachments()
                } else {

                }



            }, function errorCallback(response) {

            });

        }
    }



    $scope.sendComment = function (task_id, comment_message, user_id) {
        // console.log(task_id);
        // console.log(comment_message);
        // console.log(attach);
        // console.log(user_id);

        // var data = {
        //     comment: comment_message,
        //     task_id: task_id,
        //     attach: null,
        //     posted_by: user_id
        // }
        // console.log(data)

        if ($scope.attachment_file == 'None') {
            var data = {
                comment: comment_message,
                task_id: task_id,
                attach: null,
                posted_by: user_id
            }
            $scope.createComment(comment_message, data)

        } else {

            var data = {
                comment: comment_message,
                task_id: task_id,
                attach: $scope.attachment_file,
                posted_by: user_id
            }
            $scope.createComment(comment_message, data)

        }

    }








    $scope.open_modal_comment = function (comment) {
        console.log(comment);
        $scope.comment_for_reply = comment
        $scope.comment_id_for_reply = comment.comment_id
        $('#display_reply_comment').text(comment.comment);
    }

    $scope.replyComment = function (comment_id_for_reply, reply_message, user_id) {
        console.log(comment_id_for_reply);
        console.log(reply_message);

        var data = {
            reply: reply_message,
            comment_id: comment_id_for_reply,
            replied_by: user_id
        }

        if (reply_message == '' || reply_message == undefined) {

        } else {
            //  console.log(data);
            //$http POST function 
            $http({

                method: 'POST',
                url: myConfig.url + '/createReply.php',
                data: data

            }).then(function successCallback(response) {
                $res = response.data
                console.log($res);
                if ($res.status == 'success') {
                    //   $('#task_comment_input').val('');
                    $scope.get_task_comments()
                    $('#reply_input_form')[0].reset()
                    $('#modal-reply-comment').hide();
                    $('.modal-backdrop').hide();
                } else {

                }



            }, function errorCallback(response) {

            });

        }
    }


    $scope.get_code_desc = function (init) {
        $http({
            method: 'GET',
            url: myConfig.url + '/getCodeDescription.php?init=' + init

        }).then(function successCallback(response) {

            $scope.code_desc = response.data['0'].code_desc;
            console.log($scope.code_desc)

        }, function errorCallback(response) {

            console.log("error");

        });
    }
    $scope.get_code_desc('sta')



    $scope.saveTaskUpdate = function (taskStatus, ready_for_test, percentage_completion) {
        console.log(taskStatus)
        console.log(ready_for_test)
        console.log(percentage_completion)

        if (ready_for_test == undefined || ready_for_test == '') {
            var ready_for_test = false
        }

        if (taskStatus == '' || taskStatus == undefined) {
            var taskStatus = $scope.oneTask.status_id
            // Swal.fire({
            //     type: 'error',
            //     title: $scope.oneTask.status_id
            // })

        }
        if ((taskStatus == '58' || taskStatus == '' || taskStatus == undefined) && percentage_completion > 0) {
            Swal.fire({
                type: 'error',
                title: 'Check status',
                text: 'Change task status from "Not Started"',
            })
        } else {
            var data = {
                user_id: $scope.user_info.user_id,
                task_id: $scope.task_id,
                taskStatus: taskStatus,
                ready_for_test: ready_for_test,
                percentage_completion: percentage_completion
            }
            $scope.sendTaskUpdate(data)
        }

        // console.log(data)








    }

    $scope.sendTaskUpdate = function (data) {
        var data = data
        $http({

            method: 'POST',
            url: myConfig.url + '/updateTaskByDeveloper.php',
            data: data

        }).then(function successCallback(response) {
            $res = response.data
            console.log($res);
            if ($res.status == 'success') {

                // $scope.get_one_task()


                Swal.fire({
                    type: 'success',
                    title: 'REF-0000' + $res.task_id,
                    text: $res.message
                })

                setTimeout(function () {
                    location.reload()
                }, 700);

                var comment_message = "<span class='text-success'> New Updates: <br> [Task Status - >  <br> Go For Test - > " + data.ready_for_test + " <br> Task Completion - > " + data.percentage_completion + "] </span>"

                var data = {
                    comment: comment_message,
                    task_id: $scope.task_id,
                    attach: null,
                    posted_by: $scope.user_info.user_id
                }
                // $scope.createComment(comment_message, data)
                console.log(data)

                $scope.get_one_task()


            } else {
                Swal.fire({
                    type: 'error',
                    title: $res.status,
                    text: $res.message,
                })
            }

        }, function errorCallback(response) {

        });

    }

});