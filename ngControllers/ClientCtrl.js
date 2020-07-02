sheetApp.controller('ClientCtrl', function ($scope, $http, check_auth, myConfig, $location, $localStorage) {
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



    $scope.get_all_clients = function () {
        $http({
            method: 'GET',
            url: myConfig.url + '/getAllClientse.php'

        }).then(function successCallback(response) {

            $scope.all_clients = response.data;
            console.log($scope.all_clients)

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });
    }
    $scope.get_all_clients()




    $scope.addClient = function (client_name) {
        console.log(client_name);
        var data = {
            client: client_name
        }
        console.log(data);

        if (client_name == '' | client_name == undefined) {

        } else {

            //$http POST function
            $http({

                method: 'POST',
                url: myConfig.url + '/createClient.php',
                data: data

            }).then(function successCallback(response) {
                $res = response.data
                if ($res.status == 'success') {
                    $('#client_message_success').show();
                    setTimeout(() => {
                        $('#client_message_success').hide();
                        $('#client_name').val('');
                        $scope.get_all_clients()
                    }, 2000);
                } else {
                    $('#client_message_error').show();
                    setTimeout(() => {
                        $('#client_message_error').hide();
                    }, 4000);
                }

            }, function errorCallback(response) {
                // $res = response.data
                // console.log($res)
                // if ($res.status == 'failed') {
                $('#client_message_error').show();
                setTimeout(() => {
                    $('#client_message_error').hide();
                }, 4000);
                // }

            });

        }


    }

});