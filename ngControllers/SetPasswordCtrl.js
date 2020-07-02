sheetApp.controller('SetPasswordCtrl', function ($scope, $timeout, $location, $http, myConfig, check_auth, $routeParams) {
    $scope.hash = $routeParams.hash;
    console.log($scope.hash);

    // PROFILE PHOTO
    $scope.profile_pic = myConfig.profile_pic
    console.log($scope.profile_pic)



    $scope.get_reset_email_username = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getResetEmail.php?hash=' + $scope.hash

        }).then(function successCallback(response) {

            $scope.reset_email_username = response.data['0'];
            console.log($scope.reset_email_username)

        }, function errorCallback(response) {
            console.log(response);
        });
    }
    $scope.get_reset_email_username()


    $scope.resetPassword = function (reset_email, reset_pass_1, reset_pass_2) {
        $('#btn-setPassword').text('Loading ...');
        if (reset_pass_1 !== reset_pass_2) {

            Swal.fire({
                type: 'warning',
                title: 'Check Credential',
                text: 'Both Passwords do not match',
            })

            $('#btn-setPassword').text('Set new password');
            console.log('Password do not match');
        } else {
            console.log(reset_email);
            console.log(reset_pass_1);
            console.log(reset_pass_2);
            var data = {
                email: reset_email,
                hash_pass: reset_pass_1
            }

            //$http POST function
            $http({

                method: 'POST',
                url: myConfig.url + '/UpdatePassword.php',
                data: data

            }).then(function successCallback(response) {
                $res = response.data
                console.log($res);
                user_info = response.data
                var user_data = response.data['data']
                var user_auth = response.data['auth']
                var user_token = response.data['token']
                var user_message = response.data['status']

                localStorage.setItem('user_token', user_token);

                if (user_message == 'success') {
                    Swal.fire({
                        type: 'success',
                        title: $res.status,
                        text: $res.message
                    }).then((result) => {
                        $timeout($location.path('/dashboard_p'), 2000);
                    })



                    check_auth.save_auth(user_info);
                    console.log();


                } else {
                    Swal.fire({
                        type: 'error',
                        title: response.data['message']
                    })
                    $('#btn-setPassword').text('Set new password');
                }

                // if ($res.status == 'success') { 

                //     Swal.fire({
                //         type: 'success',
                //         title: $res.status,
                //         text: $res.message 
                //     })

                // } else {
                //     Swal.fire({
                //         type: 'error',
                //         title: $res.status,
                //         text: $res.message,
                //     })
                // }
            }, function errorCallback(response) {

                // if ($res.message == 'failed') {
                //     $('#code_message_error').show();
                //     setTimeout(() => {
                //         $('#code_message_error').hide();
                //     }, 4000);
                // }

            });

        }
    }


});