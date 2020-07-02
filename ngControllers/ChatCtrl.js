sheetApp.controller("ChatCtrl", function (
  $scope,
  $http,
  check_auth,
  myConfig,
  $location,
  $localStorage,
  $interval
) {
  check_auth.verify_auth($localStorage.user_info);
  console.log($localStorage.user_info.data);
  $scope.user_info = $localStorage.user_info.data;

  // PROFILE PHOTO
  $scope.profile_pic_true = $localStorage.profile_pic;
  $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true;
  console.log($scope.profile_pic);

  $scope.users_prifile_path = myConfig.file_url;

  //logout funtion
  $scope.logout = function () {
    check_auth.logout($scope.user_info.user_id);
  };

  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000
  });

  // end logout function

  // $scope.get_departments = function () {
  //     $http({
  //         method: 'GET',
  //         url: myConfig.url + '/getAllDepartments.php'

  //     }).then(function successCallback(response) {

  //         $scope.departments = response.data
  //         console.log($scope.departments)

  //     }, function errorCallback(response) {

  //         alert("Error. Try Again!");

  //     });
  // }
  // $scope.get_departments()

  $scope.view_user = function (user) {
    console.log(user);
    $("#reciever_id_").val(user.user_id);
    $scope.department_user_info = user;
    $scope.get_private_chat(user.user_id);
    $scope.read_message(user.user_id)
  };

  $scope.read_message = function (reciever_id) {
    $http({
      method: "GET",
      url: myConfig.url + "/readForChat.php?sender_id=" + $scope.user_info.user_id + '&reciever_id=' + reciever_id
    }).then(
      function successCallback(response) {
        var res = response.data;
        console.log(res)
        if (res.status == "success") {
          $scope.get_users();
        } else {
          Toast.fire({
            type: "error",
            title: res.message
          });
        }

      },
      function errorCallback(response) {
        Toast.fire({
          type: "error",
          title: "Network Connection Error! Users Could not loaded",
        });

      }
    );
  };
  $scope.read_message();


  $scope.get_users = function () {
    $http({
      method: "GET",
      url: myConfig.url + "/getAllUsersForChat.php?sender_id=" + $scope.user_info.user_id
    }).then(
      function successCallback(response) {
        $scope.department_users = response.data;
        $scope.users_is_read_user_messages = response.data['is_read_user_messages'];
        console.log($scope.department_users);
        console.log($scope.users_is_read_user_messages);
      },
      function errorCallback(response) {
        Toast.fire({
          type: "error",
          title: "Network Connection Error! Users Could not loaded",
        });

      }
    );
  };
  $scope.get_users();

  // $scope.get_department_users = function () {

  //     $http({
  //         method: 'GET',
  //         url: myConfig.url + '/getAllUsers.php?department_id=' + $scope.user_info.department_id

  //     }).then(function successCallback(response) {

  //         $scope.department_users = response.data;
  //         console.log($scope.department_users)

  //     }, function errorCallback(response) {
  //         Swal.fire({
  //             type: 'warning',
  //             title: 'Network Connection Erro',
  //             text: 'Users Could not loaded'
  //         })

  //     });
  // }
  // $scope.get_department_users() 

  $interval(function () {
    $scope.get_users();
  }, 4000);

  $scope.view_department = function (
    department_id,
    department_init_desc,
    department_desc
  ) {
    console.log(department_id);
    console.log(department_init_desc);
    console.log(department_desc);
    $scope.route_title = department_desc;
    $("#department_detail").show();
    $(".return_to_department").show();
    $("#department_list").hide();
    $("a#route_title").hide();
    $scope.get_users();
  };

  $scope.get_private_chat = function (member_id) {
    $http({
      method: "GET",
      url: myConfig.url +
        "/getPrivateChat.php?member_id=" +
        member_id +
        "&user_id=" +
        $scope.user_info.user_id
    }).then(
      function successCallback(response) {
        $scope.private_chat_check = response.data;
        $scope.private_chat = response.data["data"];

        console.log($scope.private_chat);
      },
      function errorCallback(response) {
        // alert("Error. Try Again!");
      }
    );
  };

  $scope.sendMessage = function () {
    var reciever_id = $("#reciever_id_").val();
    var message = $("#message_input").val();
    var sender_id = $scope.user_info.user_id;

    console.log(sender_id);
    console.log(reciever_id);
    console.log(message);
    if (reciever_id == "" || reciever_id == undefined) {
      Toast.fire({
        type: "error",
        title: "Please select a user to start chat"
      });
    } else if (message == "" || message == undefined) {
      Toast.fire({
        type: "error",
        title: "Message can not be empty"
      });
    } else {
      $scope.createMessage(sender_id, reciever_id, message);
    }
  };

  $scope.createMessage = function (sender_id, reciever_id, message) {
    var data = {
      sender_id: sender_id,
      reciever_id: reciever_id,
      message: message,
      department_id: $scope.user_info.department_id
    };
    $http({
      method: "post",
      url: myConfig.url + "/createChatMessage.php",
      data: data,
      headers: {
        "Content-Type": undefined
      }
    }).then(
      function successCallback(response) {
        $res = response.data;
        console.log($res.status);

        if ($res.status == "success") {
          $("#message_input").val('');
          $scope.get_private_chat(reciever_id);
        } else {
          Toast.fire({
            type: "error",
            title: $res.message
          });
        }
      },
      function errorCallback(response) {
        Toast.fire({
          type: "error",
          title: "Check your network connectivity"
        });
      }
    );
  };
});