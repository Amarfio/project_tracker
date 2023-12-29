sheetApp.controller('HandOverCtrl', function (
    $scope, 
    $http,
    $timeout,
    $routeParams, 
    check_auth, 
    myConfig, 
    $location, 
    $localStorage) {

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




  //function to get all developers or members of the head of department.
  $scope.get_department_devs = function (department_id) {
    // console.log('Department_id = ' + department_id) 
    $http({
        method: 'GET',
        url: myConfig.url + '/getAllDevs.php?department_id=' + department_id

    }).then(function successCallback(response) {

        $scope.dept_devs = response.data;
        console.log($scope.dept_devs)

    }, function errorCallback(response) {
        Swal.fire({
            type: 'warning',
            title: 'Network Connection Erro',
            text: 'Users Could not loaded'
        })

    });
  }
  $scope.get_department_devs($scope.user_info.department_id)

  $('#developer_').change(function(){
    var value = $(this).val();
    console.log(value);
    $scope.newUser_id = value;

    $scope.get_one_user($scope.newUser_id);
  });

  //get user details using the user's id
  $scope.get_one_user = function (user_id) {
      $http({
        method: "GET",
        url: myConfig.url + "/getOneUser.php?user_id=" + user_id
      }).then(function successCallback(response) {
        $scope.user = response.data['data'];
        $scope.user_pic = response.data['profile_pic']
        $scope.user_profile_pic = myConfig.file_url + $scope.user_pic
        console.log($scope.user_profile_pic);
        //   $scope.user = response;
        console.log("user details here: ");
        console.log($scope.user);
        console.log($scope.user_pic);
      });
  }
   
  //function to change the head of department and send email
  $scope.handOver = function(){
    var newDeptHead = $('#developer_').val()
    var handOverNote = $('#handover_note').val()
    var currentDeptHead = $scope.user_info.user_id;

    var data = {
      currentDeptHead : currentDeptHead,
      handOverNote : handOverNote,
      newDeptHead : newDeptHead,
      dept_id : $scope.user_info.department_id
    }

    // console.log(data);
    // return false;

    $http({
        method: 'POST',
        url: myConfig.url + '/handOver.php',
        data: data,
        headers: {
            'Content-Type': undefined
        },
    }).then(function successCallback(response) {
        var user_info = response.data;
        var res = response.data;
        console.log(res)

        if (res.status == "success") {

            // check_auth.save_auth(user_info);

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


});