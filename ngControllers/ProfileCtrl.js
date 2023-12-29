sheetApp.controller('ProfileCtrl', function ($scope, $http, $timeout, check_auth, myConfig, $location, $localStorage) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data


    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.signature_pic_true = $localStorage.signature_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    $scope.signature_pic = myConfig.file_url + $scope.signature_pic_true
    console.log($scope.profile_pic)

    console.log($scope.signature_pic) 



    //logout funtion 
    $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id)
    }
    // end logout function 

    $scope.get_all_countries = function () {
        $http({
            method: "GET",
            url: "https://restcountries.eu/rest/v2/all"
        }).then(
            function successCallback(response) {
                $scope.countries = response.data
                console.log($scope.countries);
            },
            function errorCallback(response) {
                // alert("Error. Try Again!");
            }
        );
    }
    $scope.get_all_countries()

    $scope.get_code_desc = function (init) {
        $http({
            method: 'GET',
            url: myConfig.url + '/getCodeDescription.php?init=' + init

        }).then(function successCallback(response) {
            
            $scope.code_desc = response.data['0'].code_desc, Number
            console.log($scope.code_desc)

        }, function errorCallback(response) {

            Swal.fire({
                type: 'warning',
                title: 'Network Connection Erro',
                text: 'Priority Could not loaded'
            })

        });
    }
    $scope.get_code_desc('ALT')


    $scope.updateUserInfo = function (emailNotice) {
        var username = $('#username').val()
        var first_name = $('#first_name').val()
        var last_name = $('#last_name').val()
        var address = $('#address').val()
        var city = $('#city').val()
        var country = $('#country').val()
        var bio = $('#bio').val()
        var email_notice = emailNotice

        //get the selected value for priority
        if (email_notice=='' || email_notice==undefined){
            var email_notice = $scope.user_info.emailNotice;
            console.log(email_notice);
            // return false;
        }

        console.log(username)
        console.log(first_name)
        console.log(last_name)
        console.log(address)
        console.log(city)
        console.log(country)
        console.log(bio)
        console.log(email_notice)
        // return false;

        var data = {
            user_id: $scope.user_info.user_id,
            username: username,
            first_name: first_name,
            last_name: last_name,
            address: address,
            city: city,
            country: country,
            bio: bio,
            email_notice : email_notice,
            isWorking: 1
        }

        $http({
            method: 'POST',
            url: myConfig.url + '/updateProfileInfo.php',
            data: data,
            headers: {
                'Content-Type': undefined
            },
        }).then(function successCallback(response) {
            var user_info = response.data;
            var res = response.data;
            console.log(res)

            if (res.status == "success") {

                check_auth.save_auth(user_info);

                $timeout(
                    window.location = "profile",
                    2000
                );

            } else {
                Swal.fire({
                    type: "error",
                    title: response.data["message"]
                });
                $("#login-signin-btn").text("Sign in");
            }

        }, function errorCallback(response) {

            // alert("Error. Try Again!");

        });

    }


    $scope.upload = function () {
        var file = $scope.uploadfile;
        var fd = new FormData();
        var files = document.getElementById('file').files[0];
        fd.append('file', files);

        $http({
            method: 'POST',
            url: myConfig.url + '/uploadProfilePic.php?user_id=' + $scope.user_info.user_id,
            data: fd,
            headers: {
                'Content-Type': undefined
            },
        }).then(function successCallback(response) {
            // Store response data
            var res = response.data;
            var res_data = response.data['data']
            if (res.status == 'success') {
                var profile_pic = res_data.profile_pic
                check_auth.profile_pic(profile_pic);
                window.location = "profile"
            }

        });
    }

    $scope.uploadSignature = function () {
        var file = $scope.uploadfile;
        var fd = new FormData();
        var files = document.getElementById('signaturefile').files[0];
        fd.append('file', files);

        $http({
            method: 'POST',
            url: myConfig.url + '/uploadSignature.php?user_id=' + $scope.user_info.user_id,
            data: fd,
            headers: {
                'Content-Type': undefined
            },
        }).then(function successCallback(response) {
            // Store response data
            var res = response.data;
            var res_data = response.data['data'];
            console.log(res_data);
            if (res.status == 'success') {
                var signature = res_data.signature
                check_auth.signature_pic(signature);
                window.location = "profile"
            }

        });
    }

});