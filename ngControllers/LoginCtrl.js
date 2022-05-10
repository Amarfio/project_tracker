sheetApp.controller("LoginCtrl", function (
    $scope,
    $http,
    $timeout,
    $location,
    check_auth,
    myConfig,
    $localStorage
) {
    check_auth.verify_auth($localStorage.user_info);
    // console.log($localStorage.user_info.data);
    // $scope.user_info = $localStorage.user_info.data
    if ($localStorage.user_info) {
        return $location.path("/dashboard_p");
    }

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    $scope.getYear = function () {
        return (current_year = new Date().getFullYear());
        // console.log((new Date).getFullYear());
    };

    $scope.login = function (email_or_username, password) {
        // console.log(email_or_username);
        // console.log(password);
        $("#login-signin-btn").text("Loading ...");

        if (
            email_or_username == "" ||
            email_or_username == undefined ||
            password == "" ||
            password == undefined
        ) {} else {
            var data = {
                username: email_or_username,
                password: password
            };

            //$http POST function
            $http({
                method: "POST",
                url: myConfig.url + "/login.php",
                data: data
            }).then(
                function successCallback(response) {
                    var user_info = response.data;
                    var profile_pic = response.data["profile_pic"];
                    var user_data = response.data["data"];
                    var user_auth = response.data["auth"];
                    var user_token = response.data["token"];
                    var user_message = response.data["status"];
                    console.log(user_info);

                    localStorage.setItem("user_token", user_token);

                    if (user_message == "success") {

                        console.log("hey " + user_message);
                        check_auth.save_auth(user_info);
                        check_auth.profile_pic(profile_pic);

                        $timeout(
                            window.location = "dashboard_p",
                            2000
                        );

                    } else {
                        Swal.fire({
                            type: "error",
                            title: response.data["message"]
                        });
                        $("#login-signin-btn").text("Sign in");
                        console.log(response.data["data"]);
                    }
                },
                function errorCallback(response) {
                    Swal.fire({
                        type: "warning",
                        title: "Something went wrong"
                    });
                    $("#login-signin-btn").text("Sign in");
                }
            );
        }
    };
});