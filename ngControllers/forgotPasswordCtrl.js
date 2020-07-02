sheetApp.controller('forgotPasswordCtrl', function ($scope, $http,$localStorage, myConfig, $routeParams) {

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    // $scope.get_reset_email_username = function () {
    //     $http({
    //         method: 'GET',
    //         url: myConfig.url + '/getResetEmail.php'

    //     }).then(function successCallback(response) {

    //         $scope.reset_email_username = response.data['0'];
    //         console.log($scope.reset_email_username)

    //     }, function errorCallback(response) {
    //         console.log(response);
    //     });
    // }
    // $scope.get_reset_email_username()  


    $scope.send_resetPassword_link = function (email) {

        console.log(email);

        if (email == '' || email == undefined) {

        } else {
            var email = email.trim()
            Swal.queue([{
                title: 'Sending reset link to  ' + email,
                onBeforeOpen: () => {
                    Swal.showLoading()
                },
            }])

            $http({
                method: 'GET',
                url: myConfig.url + '/sendResetLink.php?email=' + email

            }).then(function successCallback(response) {
                $res = response.data
                if ($res.status == 'success') {

                    Swal.fire({
                        type: 'success',
                        title: $res.status,
                        text: $res.message
                    })

                    $("#forgot_password_form")[0].reset();

                    $scope.reset_password_link = response.data;
                    console.log($scope.reset_password_link)
                } else {
                    Swal.fire({
                        type: 'error',
                        title: $res.status,
                        text: $res.message,
                    })
                }

            }, function errorCallback(response) {
                console.log(response);
                Swal.fire({
                    type: 'warning',
                    title: 'Sorry',
                    text: 'Something went wrong',
                })
            });
        }


    }



    $scope.resetPassword = function (reset_email, reset_pass_1, reset_pass_2) {
        if (reset_pass_1 !== reset_pass_2) {
            console.log('Password do not match');
        } else {
            console.log(reset_email);
            console.log(reset_pass_1);
            console.log(reset_pass_2);



        }
    }


});