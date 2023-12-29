sheetApp.controller('EditLimitCtrl', function (
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
  // end logout function 

  //code to get the user details by id
  $scope.newUser_id =  $routeParams.user_id


  //get limit details using its id
  $scope.get_one_limit = function () {
    $http({
      method: "GET",
      url: myConfig.url + "/getOneLimit.php?user_id=" + $scope.newUser_id
    }).then(function successCallback(response) {
      $scope.user = response.data['data'];
      //   $scope.user = response;
      console.log("user details here: ");
      console.log($scope.user);
    });
  }

  //call the method 
  $scope.get_one_user()



  $scope.name = 'Ampah';

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


  $scope.get_departments = function (dpt) {
      $http({
          method: 'GET',
          url: myConfig.url + '/getCodeDescription.php?init=' + dpt

      }).then(function successCallback(response) {

          $scope.departments = response.data[0].code_desc;
          console.log($scope.departments)

      }, function errorCallback(response) {

          // alert("Error. Try Again!");

      });
  }
  $scope.get_departments('dpt')

  $scope.get_roles = function (role) {
      $http({
          method: 'GET',
          url: myConfig.url + '/getCodeDescription.php?init=' + role

      }).then(function successCallback(response) {

          $scope.roles = response.data[0].code_desc;
          console.log($scope.roles)

      }, function errorCallback(response) {

          alert("Error. Try Again!");

      });
  }
  $scope.get_roles('rol')

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
    var isWorking = $('#isWorking').val()

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
    console.log(isWorking)
    // return false;

    var data = {
        user_id: $scope.newUser_id,
        username: username,
        first_name: first_name,
        last_name: last_name,
        address: address,
        city: city,
        country: country,
        bio: bio,
        email_notice : email_notice,
        isWorking: isWorking
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

            $timeout(
                window.location = 'users'
                , 5000);

            // check_auth.save_auth(user_info);

            

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

//   $scope.get_alert_options = function() {

//   }


//   $scope.get_all_users = function () {
//       $http({
//           method: 'GET',
//           url: myConfig.url + '/getAllUsers.php'

//       }).then(function successCallback(response) {

//           $scope.all_users = response.data;
//           console.log($scope.all_users)

//       }, function errorCallback(response) {

//           alert("Error. Try Again!");

//       });
//   }
//   $scope.get_all_users()

  // $scope.is_manager = 0

//   $scope.addUser = function (first_name_, last_name_, username_, gender_, email_, phone_number_, country_, city_, postal_address_, department_, is_manager_, role_, can_approve_) {
//       console.log(first_name_);
//       console.log(last_name_);
//       console.log(username_);
//       console.log(gender_);
//       console.log(email_);
//       console.log(phone_number_);
//       console.log(country_);
//       console.log(city_);
//       console.log(postal_address_);
//       console.log(department_);
//       console.log(is_manager_);
//       console.log(role_);
//       console.log(can_approve_);


//       var data = {
//           first_name: first_name_,
//           last_name: last_name_,
//           username: username_,
//           gender: gender_,
//           dept_id: department_,
//           role_id: role_,
//           can_approve: can_approve_,
//           is_dpt_head: is_manager_,
//           email: email_,
//           phone: phone_number_,
//           country: country_,
//           city: city_,
//           postal_addr: postal_address_
//       }


//       Swal.queue([{
//           title: 'Updating User ...  ',
//           showLoaderOnConfirm: true,
//           onBeforeOpen: () => {
//               Swal.showLoading()
//           },
//           showLoaderOnConfirm: true,
//       }])

//       //$http POST function
//       $http({

//           method: 'POST',
//           url: myConfig.url + '/createUser.php',
//           data: data

//       }).then(function successCallback(response) {
//           $res = response.data
//           console.log($res.status)
//           console.log($res);

//           if ($res.status == 'success') {

//               Swal.fire({
//                   type: 'success',
//                   title: $res.status,
//                   text: $res.message
//               })

//               //    Swal.fire($res.status, "success");
//               setTimeout(() => {
//                   $scope.get_all_users()
//                   $('#add_user_row').hide();
//                   $('#show_user_row').show();
//                   $('#form_add_user')[0].reset();
//                   $scope.is_manager = 0
//                   $scope.can_approve_ = 0
//               }, 2000);
//           } else {
//               Swal.fire({
//                   type: 'error',
//                   title: $res.status,
//                   text: $res.message,
//               })

//               // Swal.fire(
//           }

//       }, function errorCallback(response) {

//           Swal.fire({
//               type: 'warning',
//               title: 'Sorry',
//               text: 'Something went wrong',
//           })

//       });




//   }



});