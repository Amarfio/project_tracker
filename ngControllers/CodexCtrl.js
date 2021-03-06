sheetApp.controller('CodexCtrl', function ($scope, $http, check_auth, myConfig, $location, $localStorage) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    //logout funtion  
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function 



    $scope.get_code = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getCode.php'

        }).then(function successCallback(response) {

            $scope.codes = response.data;
            console.log($scope.codes)

        }, function errorCallback(response) {

            alert("Error. Try Again!");

        });
    }
    $scope.get_code()


    $scope.get_code_desc = function (init, code_desc) {
        $('#code_table').hide();
        $('.code_title').hide();
        $('#code_description_table').show();
        $('a.return_to_code').show();
        $http({
            method: 'GET',
            url: myConfig.url + '/getCodeDescription.php?init=' + init

        }).then(function successCallback(response) {

            $scope.code_desc = response.data['0'];
            console.log($scope.code_desc)

        }, function errorCallback(response) {

            $scope.code_desc = {
                init: init,
                name: code_desc
            }

        });
    }


    $scope.show_code_model = function (init) {
        $scope.code_init = init
        console.log($scope.code_init);
        // $('#code_heading').text(code.init);
    }

    // ADD CODE 

    $scope.addCode = function (init, name) {
        console.log(init, name)
        var data = {
            user_id: $scope.user_info.user_id,
            init: init,
            name: name
        }
        console.log(data);

        if (init == '' | init == undefined | name == '' | name == undefined) {

        } else {

            //$http POST function
            $http({

                method: 'POST',
                url: myConfig.url + '/createCode.php',
                data: data

            }).then(function successCallback(response) {
                $res = response.data
                if ($res.status == 'success') {
                    $('#code_message_success').show();
                    setTimeout(() => {
                        $('#code_message_success').hide();
                        $('#codex_init_').val('');
                        $('#code_name_').val('');
                        // $('#modal-add-codex').hide();
                        // $('.modal-backdrop').hide();
                        $scope.get_code()
                    }, 2000);
                } else {
                    $('#code_message_error').show();
                    setTimeout(() => {
                        $('#code_message_error').hide();
                    }, 4000);
                }

            }, function errorCallback(response) {
                $res = response.data
                console.log($res)
                if ($res.message == 'failed') {
                    $('#code_message_error').show();
                    setTimeout(() => {
                        $('#code_message_error').hide();
                    }, 4000);
                }

            });

        }


    }

    // END ADD CODE 




    // ADD CODE DESCRIPTION

    $scope.addCodeDescription = function (init, init_desc, description) {


        if (init == '' | init == undefined | init_desc == '' | init_desc == undefined | description == '' | description == undefined) {

        } else {
            console.log(init, description)
            var data = {
                user_id: $scope.user_info.user_id,
                init: init,
                init_desc: init_desc,
                description: description
            }

            // $http POST function
            $http({

                method: 'POST',
                url: myConfig.url + '/createCodeDesc.php',
                data: data

            }).then(function successCallback(response) {
                $res = response.data
                if ($res.status == 'success') {
                    $('#code_description_message_success').show();
                    $('#code_description_message_success').html('<strong>' + $res.message + '</strong>');
                    setTimeout(() => {
                        $('#code_description_message_success').hide();
                        $('#code_init_').val('');
                        $('#code_description_').val('');
                        $('#codex_init_description_').val('');
                        // $('#modal-add-codex-description').hide();
                        // $('.modal-backdrop').hide();
                        $scope.get_code_desc(data.init)
                    }, 2000);
                } else {
                    $('#code_description_message_error').show();
                    // $('#code_description_message_error').text($res.message);
                    $('#code_description_message_error').html('<strong>' + $res.message + '</strong>');
                    setTimeout(() => {
                        $('#code_description_message_error').hide();
                    }, 4000);
                }

            }, function errorCallback(response) {
                $res = response.data
                if ($res.message == 'failed') {
                    $('#code_description_message_error').show();
                    setTimeout(() => {
                        $('#code_description_message_error').hide();
                    }, 4000);
                }

            });

        }


    }

    // END ADD CODE DESCRIPTION




});