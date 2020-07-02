sheetApp.controller("EditProjectCtrl", function (
  $scope,
  $http,
  $routeParams,
  $timeout,
  check_auth,
  myConfig,
  get_services,
  $location,
  $localStorage,
  dataService
) {
  check_auth.verify_auth($localStorage.user_info);
  console.log($localStorage.user_info.data);
  $scope.user_info = $localStorage.user_info.data;
  $scope.user_id = $localStorage.user_info.data["user_id"];
  $scope.all_departments = get_services.get_departments("dpt");

  // PROFILE PHOTO
  $scope.profile_pic_true = $localStorage.profile_pic
  $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
  console.log($scope.profile_pic)


  $scope.project_id = $routeParams.project_id

  //logout funtion
  $scope.logout = function () {
    check_auth.logout($scope.user_info.user_id);
  };
  // end logout function

  $scope.get_one_project = function () {
    $http({
      method: "GET",
      url: myConfig.url + "/getOneProject.php?project_id=" + $scope.project_id
    }).then(function successCallback(response) {
      $scope.project = response.data['data'][0];
      //   $scope.project = response;
      console.log($scope.project);
    });
  }

  $scope.get_one_project()

  // console.log($scope.all_departments)

  // $scope.get_departments = function (code_desc, file){
  //     // console.log(code_desc, file)
  //     // $scope.departments = get_services.get_one_code_description(code_desc, file)
  //     console.log(get_services.get_one_code_description(code_desc, file))
  // }
  // $scope.get_departments('dpt', '/getCodeDescription.php?init=')

  $scope.get_versions = function (ver) {
    $http({
      method: "GET",
      url: myConfig.url + "/getCodeDescription.php?init=" + ver
    }).then(
      function successCallback(response) {
        $scope.versions = response.data[0].code_desc;
        console.log($scope.versions);
      },
      function errorCallback(response) {
        alert("Error. Try Again!");
      }
    );
  };
  $scope.get_versions("ver");

  $scope.get_departments = function (dpt) {
    $http({
      method: "GET",
      url: myConfig.url + "/getCodeDescription.php?init=" + dpt
    }).then(
      function successCallback(response) {
        $scope.departments = response.data[0].code_desc;
        console.log($scope.departments);
      },
      function errorCallback(response) {
        // alert("Error. Try Again!");
      }
    );
  };
  $scope.get_departments("dpt");

  $scope.UpdateProject = function (
    project_name_,
    version_no_,
    department_id_,
    start_date_,
    end_date_,
    project_description_
  ) {

    var project_name = $('#project_name').val();
    // var version_no = $('#project_version_no').val();
    // var department_id = $('#project_department_id').val();
    var start_date = $('#project_start_date').val();
    var end_date = $('#project_end_date').val();
    var project_description = $('#project_description').val();


    var data = {
      project_id: $scope.project_id,
      project_name: project_name,
      version_no: version_no_,
      project_description: project_description,
      dept_id: department_id_,
      user_id: $scope.user_id,
      start_date: new Date(start_date),
      end_date: new Date(end_date)
    };


    Swal.queue([{
      title: "Updating a project ...  ",
      // showLoaderOnConfirm: true,
      onBeforeOpen: () => {
        Swal.showLoading();
      },
      showLoaderOnConfirm: true
    }]);

    var sendRequest = function () {
      //$http POST function
      $http({
        method: "POST",
        url: myConfig.url + "/updateProject.php",
        data: data
      }).then(
        function successCallback(response) {
          var $res = response.data;
          console.log($res.status);

          setTimeout(function () {
            if ($res.status == "success") {
              Swal.fire({
                type: "success",
                title: "PROJ-0000" + $res.project_id,
                text: $res.message
              });
              // // $('#add_project_form')[0].reset();

              // //    Swal.fire($res.status, "success");

              $("#add_project_form")[0].reset();

              $timeout(
                window.location = "project/ " + $scope.project_id,
                // $location.path('/add_task?project_id=' + $res.project_id + '&project_name=' + project_name_ + '&department_id=' + department_id_)

                2000
              );
            } else {
              Swal.fire({
                type: "error",
                title: $res.status,
                text: $res.message
              });
            }
          }, 4000);
        },
        function errorCallback(response) {
          Swal.fire({
            type: "warning",
            title: "Sorry",
            text: "Something went wrong"
          });
        }
      );
    };

    if (end_date_ < start_date_) {
      console.log("date can not be less");
      Swal.fire({
        type: "error",
        title: "Invalid Date",
        text: "End date can go behind start date"
      });
    } else {
      sendRequest();
    }
  };
});