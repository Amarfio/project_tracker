sheetApp.controller(
  "TaskCtrl",
  function (
    $scope,
    $http,
    $routeParams,
    check_auth,
    myConfig,
    $location,
    $localStorage
  ) {
    check_auth.verify_auth($localStorage.user_info);
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data;
    $scope.user_id = $localStorage.user_info.data["user_id"];

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic;
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true;
    console.log($scope.profile_pic);
    $scope.myConfig_file_url = myConfig.file_url;

    //logout funtion
    $scope.logout = function () {
      check_auth.logout();
    };
    // end logout function

    $scope.get_project_status = function (init) {
      $http({
        method: "GET",
        url: myConfig.url + "/getProjectStatus.php?init=" + init,
      }).then(
        function successCallback(response) {
          $scope.project_status_list = response.data;
          console.log($scope.project_status_list);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };
    $scope.get_project_status("psta");

    console.log(($scope.project_id = $routeParams.project_id));

    $scope.get_one_project = function () {
      // window.location.reload();
      $http({
        method: "GET",
        url:
          myConfig.url + "/getOneProject.php?project_id=" + $scope.project_id,
      }).then(
        function successCallback(response) {
          if (!response.data["data"]) {
            return $location.path("/404");
          }

          $scope.project_avg = response.data["project_avg_percentage"];
          $scope.project = response.data["data"][0];
          $scope.getStatus = response.data["data"][0].project_status;
          // $scope.department = response.data['data'][0].department
          console.log($scope.project.department_id);
          console.log($scope.project);
          console.log($scope.getStatus);
          $scope.tasks = $scope.project.tasks;
          console.log("here we dey for tasks");
          console.log($scope.tasks);

          if ($scope.project_avg >= 80) {
            $scope.percentage_color = "success";
          } else if ($scope.project_avg >= 60) {
            $scope.percentage_color = "info";
          } else if ($scope.project_avg >= 40) {
            $scope.percentage_color = "primary";
          } else if ($scope.project_avg >= 20) {
            $scope.percentage_color = "warning";
          } else {
            $scope.percentage_color = "danger";
          }

          // if ($scope.user_info.is_dept_head == '1' && $scope.user_info.department_id == $scope.project.department_id && $scope.project.is_approved == '0') {
          //     $scope.approve_btn = true
          // }
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };
    $scope.get_one_project();

    $scope.get_departments = function () {
      $http({
        method: "GET",
        url: myConfig.url + "/getAllDepartments.php",
      }).then(
        function successCallback(response) {
          $scope.departments = response.data;
          console.log($scope.departments);
        },
        function errorCallback(response) {
          alert("Error. Try Again!");
        }
      );
    };
    $scope.get_departments();

    $scope.getDetailsWithId = function (task_id, assigned_to) {
      console.log("test task id ", task_id);
      console.log("test assigned to id", assigned_to);

      $http({
        method: "GET",
        url:
          myConfig.url +
          "/getAllConflictsTasksById.php?task_id=" +
          task_id +
          "&assigned_to=" +
          assigned_to,
      }).then(
        function successCallback(response) {
          var taskClashes = response.data["data"];
          // console.log("first try ")
          console.log(taskClashes);
          $scope.taskClashes = taskClashes;
          console.log($scope.taskClashes);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };

    $scope.get_statics_developer_for_assigner_and_admin = function (init) {
      var _url =
        myConfig.url +
        "/getCodeStatusCount_For_Assigner_&_Admin.php?init=" +
        init;

      $http({
        method: "GET",
        url: _url,
      }).then(
        function successCallback(response) {
          $scope.total_tasks = response.data[0].total_tasks;
          $scope.approved_project = response.data[0].approved_project;
          $scope.unapproved_project = response.data[0].unapproved_project;
          $scope.status_stats = response.data[0].code_desc;
          // console.log($scope.status_stats)
          // console.log($scope.approved_project)
          // console.log($scope.unapproved_project)
          /**
           * ! to determine where the card id developer or admin or assigner
           */
          $scope._user_info_role = "assigner_and_admin";
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };

    // $scope.approve_project = function (approved_by, project_id) {
    //     console.log(approved_by)
    //     console.log(project_id)

    //     $http({
    //         method: 'GET',
    //         url: myConfig.url + '/approve_project.php?approved_by=' + approved_by + '&project_id=' + project_id

    //     }).then(function successCallback(response) {
    //         var $res = response.data
    //         console.log(response.data)
    //         if ($res.status == 'success') {
    //             Swal.fire({
    //                 type: 'success',
    //                 title: 'PROJ-0000' + $res.project_id,
    //                 text: $res.message
    //             })

    //             $scope.get_one_project()
    //         }

    //     }, function errorCallback(response) {

    //         // alert("Error. Try Again!");

    //     });

    // }

    $scope.submit_for_approval = function () {
      $(".modal").hide();

      let todaysDate = new Date();
      todaysDate.setHours(0, 0, 0, 0); // Reset time to midnight

      // Example start date string
      // let start_date_ = "2025-08-20"; // YYYY-MM-DD format

      // Convert start_date_ to Date object
      let start_date_ = new Date($scope.project.start_date);
      start_date_.setHours(0, 0, 0, 0);

      if (start_date_ <= todaysDate) {
        Swal.fire({
          type: "error",
          title: "Invalid Date",
          text: "Start date must be 3 days later than today for the approval!!",
        });
        console.log("testing again for 3 days thing");
        return false;
      }

      Swal.queue([
        {
          title: "Proccessing ...  ",
          // showLoaderOnConfirm: true,
          onBeforeOpen: () => {
            Swal.showLoading();
          },
          showLoaderOnConfirm: true,
        },
      ]);

      $http({
        method: "GET",
        url:
          myConfig.url +
          "/submit_project_for_approval.php?project_id=" +
          $scope.project_id,
      }).then(
        function successCallback(response) {
          var $res = response.data;
          console.log(response.data);
          if ($res.status == "success") {
            Swal.fire({
              type: "success",
              title: "PROJ-0000" + $res.project_id,
              text: $res.message,
            });

            $scope.get_one_project();
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else if ($res.status == "failed") {
            Swal.fire({
              type: "error",
              title: $res.message,
            });

            $scope.get_one_project();
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          }
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };

    // if ($scope.getStatus == 'drafts' && $scope.user_info.role == 'assigner') {
    //     $scope.submit_project_for_approval = true

    // }

    // if ($scope.user_info.can_approve == '1' && $scope.getStatus == 'unapproved') {
    //     $scope.approve_this_project = true
    // }

    $scope.reassign_project = function (department_id) {
      var comment = $("#comment_for_reassign").val();
      var department_id = department_id;
      console.log(department_id);

      if (
        comment == undefined ||
        comment == "" ||
        department_id == undefined ||
        department_id == ""
      ) {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });

        Toast.fire({
          type: "error",
          title: "Field can not be empty",
        });
      } else {
        $(".modal").hide();
        Swal.queue([
          {
            title: "Processing Approval ...  ",
            // showLoaderOnConfirm: true,
            onBeforeOpen: () => {
              Swal.showLoading();
            },
            showLoaderOnConfirm: true,
          },
        ]);

        $http({
          method: "GET",
          url:
            myConfig.url +
            "/reassign_project.php?project_id=" +
            $scope.project_id +
            "&reassignedBy=" +
            $scope.user_info.user_id +
            "&department_id=" +
            department_id +
            "&comment=" +
            comment +
            "&user_id=" +
            $scope.user_info.user_id,
        }).then(
          function successCallback(response) {
            var $res = response.data;
            console.log(response.data);
            if ($res.status == "success") {
              Swal.fire({
                type: "success",
                title: "PROJ-0000" + $res.project_id,
                text: $res.message,
              });

              $scope.get_one_project();
              setTimeout(() => {
                window.location.reload();
              }, 2000);
            }
            if ($res.status == "failed") {
              Swal.fire({
                type: "error",
                title: "PROJ-0000" + $res.project_id,
                text: $res.message,
              });

              $scope.get_one_project();
              setTimeout(() => {
                window.location.reload();
              }, 2000);
            }
          },
          function errorCallback(response) {
            // alert("Error. Try Again!");
          }
        );
      }
    };

    $scope.approve_project = function () {
      $(".modal").hide();

      var comment = $("#comment_approve_or_reject").val();

      if (comment == undefined || comment == "") {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });

        Toast.fire({
          type: "error",
          title: "Comment cannot be empty",
        });
      } else {
        $(".modal").hide();
        Swal.queue([
          {
            title: "Processing Approval ...  ",
            // showLoaderOnConfirm: true,
            onBeforeOpen: () => {
              Swal.showLoading();
            },
            showLoaderOnConfirm: true,
          },
        ]);

        $http({
          method: "GET",
          url:
            myConfig.url +
            "/approve_project.php?project_id=" +
            $scope.project_id +
            "&approvedBy=" +
            $scope.user_info.user_id +
            "&department_id=" +
            $scope.project.department_id +
            "&comment=" +
            comment,
        }).then(
          function successCallback(response) {
            var $res = response.data;
            console.log(response.data);
            if ($res.status == "success") {
              Swal.fire({
                type: "success",
                title: "PROJ-0000" + $res.project_id,
                text: $res.message,
              });

              $scope.get_one_project();
              setTimeout(() => {
                window.location.reload();
              }, 2000);
            }
            if ($res.status == "failed") {
              Swal.fire({
                type: "error",
                title: "PROJ-0000" + $res.project_id,
                text: $res.message,
              });

              $scope.get_one_project();
              setTimeout(() => {
                window.location.reload();
              }, 2000);
            }
          },
          function errorCallback(response) {
            // alert("Error. Try Again!");
          }
        );
      }
    };

    $scope.reject_project = function () {
      var comment = $("#comment_approve_or_reject").val();

      if (comment == undefined || comment == "") {
        // Swal.fire({
        //     type: 'error',
        //     title: 'hey',
        //     text: 'Emptyp',
        //     toast: true
        // })
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });

        Toast.fire({
          type: "error",
          title: "Comment can be empty",
        });
      } else {
        $(".modal").hide();
        Swal.queue([
          {
            title: "Send Comment ...  ",
            // showLoaderOnConfirm: true,
            onBeforeOpen: () => {
              Swal.showLoading();
            },
            showLoaderOnConfirm: true,
          },
        ]);

        $http({
          method: "GET",
          url:
            myConfig.url +
            "/reject_project.php?project_id=" +
            $scope.project_id +
            "&approvedBy=" +
            $scope.user_info.user_id +
            "&department_id=" +
            $scope.project.department_id +
            "&comment=" +
            comment,
        }).then(
          function successCallback(response) {
            var $res = response.data;
            console.log(response.data);
            if ($res.status == "success") {
              Swal.fire({
                type: "success",
                title: $res.message,
              });
              setTimeout(() => {
                window.location.reload();
              }, 2000);

              $scope.get_one_project();
            }
            if ($res.status == "failed") {
              Swal.fire({
                type: "error",
                title: $res.message,
              });

              setTimeout(() => {
                window.location.reload();
              }, 2000);

              $scope.get_one_project();
            }
          },
          function errorCallback(response) {
            // alert("Error. Try Again!");
          }
        );
      }
    };

    //method to suspend project
    $scope.suspend_project = function () {
      var comment = $("#comment_approve_or_reject").val();

      if (comment == undefined || comment == "") {
        // Swal.fire({
        //     type: 'error',
        //     title: 'hey',
        //     text: 'Emptyp',
        //     toast: true
        // })
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });

        Toast.fire({
          type: "error",
          title: "Comment cannot be empty",
        });
      } else {
        $(".modal").hide();
        Swal.queue([
          {
            title: "Send Comment ...  ",
            // showLoaderOnConfirm: true,
            onBeforeOpen: () => {
              Swal.showLoading();
            },
            showLoaderOnConfirm: true,
          },
        ]);

        $http({
          method: "GET",
          url:
            myConfig.url +
            "/suspend_project.php?project_id=" +
            $scope.project_id +
            "&approvedBy=" +
            $scope.user_info.user_id +
            "&department_id=" +
            $scope.project.department_id +
            "&comment=" +
            comment,
        }).then(
          function successCallback(response) {
            var $res = response.data;
            console.log(response.data);
            if ($res.status == "success") {
              Swal.fire({
                type: "success",
                title: $res.message,
              });

              setTimeout(() => {
                window.location.reload();
              }, 2000);

              $scope.get_one_project();
            }
            if ($res.status == "failed") {
              Swal.fire({
                type: "error",
                title: $res.message,
              });
              setTimeout(() => {
                window.location.reload();
              }, 2000);

              $scope.get_one_project();
            }
          },
          function errorCallback(response) {
            // alert("Error. Try Again!");
          }
        );
      }
    };

    //method to archive project
    $scope.archive_project = function () {
      var comment = $("#comment_approve_or_reject").val();

      if (comment == undefined || comment == "") {
        // Swal.fire({
        //     type: 'error',
        //     title: 'hey',
        //     text: 'Emptyp',
        //     toast: true
        // })
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });

        Toast.fire({
          type: "error",
          title: "Comment cannot be empty",
        });
      } else {
        $(".modal").hide();
        Swal.queue([
          {
            title: "Send Comment ...  ",
            // showLoaderOnConfirm: true,
            onBeforeOpen: () => {
              Swal.showLoading();
            },
            showLoaderOnConfirm: true,
          },
        ]);

        $http({
          method: "GET",
          url:
            myConfig.url +
            "/archive_project.php?project_id=" +
            $scope.project_id +
            "&approvedBy=" +
            $scope.user_info.user_id +
            "&department_id=" +
            $scope.project.department_id +
            "&comment=" +
            comment,
        }).then(
          function successCallback(response) {
            var $res = response.data;
            console.log(response.data);
            if ($res.status == "success") {
              Swal.fire({
                type: "success",
                title: $res.message,
              });
              setTimeout(() => {
                window.location.reload();
              }, 2000);

              $scope.get_one_project();
            }
            if ($res.status == "failed") {
              Swal.fire({
                type: "error",
                title: $res.message,
              });

              setTimeout(() => {
                window.location.reload();
              }, 2000);

              $scope.get_one_project();
            }
          },
          function errorCallback(response) {
            // alert("Error. Try Again!");
          }
        );
      }
    };

    $scope.get_tasks_by_status_id = function (
      user_id,
      status_id,
      statistics_description_name,
      _user_info_role,
      department_id
    ) {
      // $scope.get_tasks_by_status_id = function (user_id) {
      console.log(status_id, "the user id ");
      // return false;
      console.log("by status id: " + _user_info_role);
      console.log("Get the data by the status specified", status_id);
      // return false;
      // console.log(user_id)
      // console.log(status_id)
      $scope.statistics_description_name = statistics_description_name;

      var _url = "";
      if (_user_info_role == "developer") {
        _url =
          myConfig.url +
          "/getOneProjectTasks_ForDeveloper.php?user_id=" +
          user_id +
          "&status_id=" +
          status_id +
          "&project_id=" +
          $scope.project_id;
        // console.log("url that", _url); return false
      } else if (_user_info_role == "assigner_and_admin") {
        _url =
          myConfig.url +
          "/getOneProjectTasks_ForAssigner_&_Admin.php?status_id=" +
          status_id +
          "&project_id=" +
          $scope.project_id;
        console.log("url that again", _url);
        // return false;
      } else if (_user_info_role == "admin") {
        _url =
          myConfig.url +
          "/getOneProjectTasks_ForAssigner_&_Admin.php?status_id=" +
          status_id +
          "&project_id=" +
          $scope.project_id;
        console.log("url that again", _url);
        // return false;
      } else if (_user_info_role == "department_head") {
        _url =
          myConfig.url +
          "/getOneProjectTasks_ForDepartmentHead.php?status_id=" +
          status_id +
          "&department_id=" +
          department_id +
          "&project_id=" +
          $scope.project_id;
      }

      // console.log(_url); return false;

      $http({
        method: "GET",
        url: _url,
      }).then(
        function successCallback(response) {
          $scope.project = response.data["data"][0];
          $scope.tasks = $scope.project.tasks;
          console.log($scope.tasks);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };

    $scope.get_statics_developer_for_developer = function (
      init,
      department_id,
      is_dept_head,
      user_id
    ) {
      console.log(init);
      console.log(department_id);
      console.log(is_dept_head);
      console.log(user_id);

      var _url =
        myConfig.url +
        "/getCodeStatusCountOneProject_ForDeveloper.php?init=" +
        init +
        "&department_id=" +
        department_id +
        "&is_dept_head=" +
        is_dept_head +
        "&user_id=" +
        user_id +
        "&project_id=" +
        $scope.project_id;

      $http({
        method: "GET",
        url: _url,
      }).then(
        function successCallback(response) {
          $scope.total_tasks = response.data[0].total_tasks;
          $scope.approved_tasks = response.data[0].approved_tasks;
          $scope.unapproved_tasks = response.data[0].unapproved_tasks;
          $scope.status_stats = response.data[0].code_desc;
          // console.log($scope.status_stats)
          // console.log($scope.approved_project)
          // console.log($scope.unapproved_project)
          /**
           * ! to determine where the card id developer or admin or assigner
           */
          $scope._user_info_role = "developer";
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };
    // $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, $scope.user_info.is_dept_head, $scope.user_info.user_id, $scope.user_info.role)

    $scope.get_statics_developer_for_department_head = function (
      init,
      department_id
    ) {
      console.log(init);
      console.log(department_id);

      var _url =
        myConfig.url +
        "/getCodeStatusCountOfOneProject_ForDepartmentHead.php?init=" +
        init +
        "&department_id=" +
        department_id +
        "&project_id=" +
        $scope.project_id;

      $http({
        method: "GET",
        url: _url,
      }).then(
        function successCallback(response) {
          $scope.total_tasks = response.data[0].total_tasks;
          $scope.approved_project = response.data[0].approved_project;
          $scope.unapproved_project = response.data[0].unapproved_project;
          $scope.status_stats = response.data[0].code_desc;
          // console.log($scope.status_stats)
          // console.log($scope.approved_project)
          // console.log($scope.unapproved_project)
          /**
           * ! to determine where the card id developer or admin or assigner
           */
          $scope._user_info_role = "department_head";
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };
    // $scope.get_statics_developer_for_department_head('sta', $scope.user_info.department_id)

    $scope.get_statics_developer_for_assigner_and_admin = function (init) {
      var _url =
        myConfig.url +
        "/getCodeStatusCountOfOneProject_Project_For_Assigner_&_Admin.php?init=" +
        init +
        "&project_id=" +
        $scope.project_id;

      $http({
        method: "GET",
        url: _url,
      }).then(
        function successCallback(response) {
          $scope.total_tasks = response.data[0].total_tasks;
          $scope.approved_tasks = response.data[0].approved_tasks;
          $scope.unapproved_tasks = response.data[0].unapproved_tasks;
          $scope.status_stats = response.data[0].code_desc;
          console.log(response.data);
          // console.log($scope.status_stats)
          // console.log($scope.approved_tasks)
          // console.log($scope.unapproved_tasks)
          /**
           * ! to determine where the card id developer or admin or assigner
           */
          $scope._user_info_role = "assigner_and_admin";
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    };

    if (
      ($scope.user_info.role == "admin" ||
        $scope.user_info.role == "assigner") &&
      ($scope.user_info.is_dept_head == 1 || $scope.user_info.is_dept_head == 0)
    ) {
      $scope.get_statics_developer_for_assigner_and_admin("sta");
      // $scope.get_total_projects()
    }
    if (
      $scope.user_info.role == "developer" &&
      $scope.user_info.is_dept_head == 1
    ) {
      $scope.get_statics_developer_for_department_head(
        "sta",
        $scope.user_info.department_id
      );
    }
    // // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0 && $scope.user_info.role == 'assigner') {
    // //     $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
    // // }
    // if ($scope.user_info.role == 'developer' && $scope.user_info.is_dept_head == 0) {
    //     $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, 0, $scope.user_info.user_id)
    // }

    // $scope.get_statics_developer_for_assigner_and_admin('sta')
    // $scope.get_statics_developer_for_developer('sta', $scope.user_info.department_id, $scope.user_info.is_dept_head, $scope.user_info.user_id)

    //task related methods
    $scope.get_all_clients = function () {
      $http({
        method: "GET",
        url: myConfig.url + "/getAllClients.php",
      }).then(
        function successCallback(response) {
          $scope.all_clients = response.data;
          console.log($scope.all_clients);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
          Swal.fire({
            type: "warning",
            title: "Network Connection Erro",
            text: "Clients Could not loaded",
          });
        }
      );
    };
    $scope.get_all_clients();

    //get all related tasks
    $scope.get_related_tasks = function () {
      $http({
        method: "GET",
        url:
          myConfig.url + "/getOneProject.php?project_id=" + $scope.project_id,
      }).then(
        function successCallback(response) {
          // console.log(response?.data?.data[0]?.priority, "the response");
          // return false;
          $scope.client_id = response?.data?.data[0]?.client_id;
          $scope.all_tasks = $scope.removeCompletedTasks(
            response?.data?.data[0]?.tasks
          );
          console.log("all tasks");
          console.log($scope.all_tasks);
          $scope.priority_ = response?.data?.data[0]?.priority;
          console.log("priority here:::", $scope.priority_);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
          Swal.fire({
            type: "warning",
            title: "Network Connection Erro",
            text: "Clients Could not loaded",
          });
        }
      );
    };
    $scope.get_related_tasks();

    // ATTACHMENT ICON FUNCTION

    $scope.attachment_file = "None";
    $scope.get_file_input = function () {
      // $("#upload_file").click(function () {
      //     $("#id_file_field").trigger('click');
      // });
      var fileName = "";
      $("#attached-file").change(function () {
        var value = this.value;
        fileName =
          typeof value == "string" ? value.match(/[^\/\\]+$/)[0] : value[0];
        console.log(fileName);
        $scope.attachment_file = fileName;
        // $('#attached-text').text(fileName);
      });
    };

    $scope.get_code_desc = function (init) {
      $http({
        method: "GET",
        url: myConfig.url + "/getCodeDescription.php?init=" + init,
      }).then(
        function successCallback(response) {
          $scope.code_desc = response.data["0"].code_desc;
          console.log($scope.code_desc);
        },
        function errorCallback(response) {
          Swal.fire({
            type: "warning",
            title: "Network Connection Erro",
            text: "Priority Could not loaded",
          });
        }
      );
    };
    $scope.get_code_desc("pri");

    $scope.get_departments = function (dpt) {
      $http({
        method: "GET",
        url: myConfig.url + "/getCodeDescription.php?init=" + dpt,
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

    $scope.get_statuses = function(stat){
      $http({
        method: "GET",
        url: myConfig.url + "/getCodeDescription.php?init=" + stat,
      }).then(
        function successCallback(response) {
          $scope.statuses = response.data[0].code_desc;
          console.log($scope.statuses);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        }
      );
    }
    $scope.get_statuses("sta");

    $scope.get_all_users = function (department_id) {
      // console.log('Department_id = ' + department_id)
      $http({
        method: "GET",
        // url: myConfig.url + '/getAllUsers.php'
        url: myConfig.url + "/getAllDevs.php",
      }).then(
        function successCallback(response) {
          $scope.all_users = response.data;
          console.log($scope.all_users, "users here");
        },
        function errorCallback(response) {
          Swal.fire({
            type: "warning",
            title: "Network Connection Erro",
            text: "Users Could not loaded",
          });
        }
      );
    };
    $scope.get_all_users($scope.url_department_id);

    //code to upload a file
    //code to get uploaded file value
    $scope.upload = function () {
      var file = $scope.uploadfile;
      var fd = new FormData();
      var files = document.getElementById("attached-file").files[0];
      fd.append("file", files);

      $http({
        method: "post",
        url: myConfig.url + "/uploadFile.php",
        data: fd,
        headers: {
          "Content-Type": undefined,
        },
      }).then(function successCallback(response) {
        // Store response data
        $scope.response = response.data;
        console.log($scope.response);
        // $('#show_attach_name').text('None');
        // $scope.attachment_file = 'None'
      });
    };

    $scope.createTask = function (
      project_id_,
      task_,
      priority_,
      developer_,
      client_id_,
      start_date_,
      end_date_,
      risk_,
      department_id_,
      dep_task
    ) {
      // console.log(project_id_)
      // console.log(priority_)
      // console.log(task_)
      // console.log(developer_)
      // console.log(client_id_)
      // console.log(start_date_)
      // console.log(end_date_)
      $("#addTaskBtn").prop("disabled", true);
      console.log(department_id_, "dept here!!!!");
      console.log("This is start date:");
      console.log(new Date($scope.p_start_date));
      console.log("This is end date:");
      console.log($scope.p_end_date);
      // return false;
      var p_start_date = Date.parse($scope.project.start_date);
      console.log(p_start_date);
      var p_end_date = Date.parse($scope.project.end_date);
      var start_date_ = Date.parse($scope.start_date_);
      console.log(start_date_);
      // return false;
      var end_date_ = Date.parse($scope.end_date_);

      // return false;
      var sendRequest = function () {
        // $http POST function
        $http({
          method: "POST",
          url: myConfig.url + "/createTask.php",
          data: data,
        }).then(
          function successCallback(response) {
            $res = response.data;
            console.log($res.status);

            $("#modal-adding-task").hide();
            if ($res.status == "success") {
              Swal.fire({
                type: "success",
                title: "REFJ-0000" + $res.task_id,
                text: $res.message,
              });

              //    Swal.fire($res.status, "success");
              // document.getElementById("modal-adding-task").style.display =
              //   "none";
              setTimeout(() => {
                window.location.reload();
              }, 2000);

              // setTimeout(() => {
              //   // $scope.get_all_users()
              //   $("#add_task_form")[0].reset();
              //   $("#customRadio4").val("");
              //   $("#customRadio6").val("");
              //   // $timeout(window.location = '', 2000);
              //   $timeout(
              //     $location.path("/project/" + $scope.url_project_id),
              //     1000
              //   );
              // }, 2000);
            } else {
              Swal.fire({
                type: "error",
                title: $res.status,
                text: $res.message,
              });

              ("#addTaskBtn").prop("disabled", false);
              // Swal.fire($res.message);
              // setTimeout(() => {
              //     // $('#client_message_error').hide();
              // }, 4000);
            }
          },
          function errorCallback(response) {
            // $res = response.data
            // console.log($res)
            Swal.fire({
              type: "warning",
              title: "Sorry",
              text: "Something went wrong",
            });

            ("#addTaskBtn").prop("disabled", false);
          }
        );
      };
      //check this code
      // console.log(p_start_date);
      // console.log(end_date_);

      // if ( p_start_date > start_date_){
      //     console.log("true");
      // }
      // else {
      //     console.log("false");
      // }
      // return false;

      // if ( p_start_date > start_date_) {
      //     Swal.fire({
      //         type: 'error',
      //         title: 'Invalid Date',
      //         text: 'Task start date can not be earlier than Project start date',
      //     })
      // } else
      if (p_end_date < start_date_) {
        Swal.fire({
          type: "error",
          title: "Invalid Date",
          text: "Project end date can not be earlier than task start date",
        });
      } else if (p_end_date < end_date_) {
        Swal.fire({
          type: "error",
          title: "Invalid Date",
          text: "Task end date can not go beyond than Project end date",
        });
      } else if (end_date_ < start_date_) {
        Swal.fire({
          type: "error",
          title: "Invalid Date",
          text: "Task end date can not be less than task start date",
        });
      } else if (p_start_date > end_date_) {
        Swal.fire({
          type: "error",
          title: "Invalid Date",
          text: "Project Start Date Cannot Greater Than Task End Date",
        });
      } else {
        start_date_ = new Date(start_date_).toISOString().substring(0, 10);
        console.log(start_date_);
        end_date_ = new Date(end_date_).toISOString().substring(0, 10);
        console.log(end_date_);
        // return false;
        var data = {
          user_id: $scope.user_info.user_id,
          project_id: project_id_.trim(),
          priority: priority_,
          task_name: task_.trim(),
          client_id: $scope.client_id,
          assigned_to: developer_.trim(),
          assigned_by: $scope.user_id.trim(),
          dept: department_id_,
          t_start_date: start_date_,
          t_end_date: end_date_,
          p_start_date: p_start_date,
          p_end_date: p_end_date,
          risk: risk_,
          fileName: "",
          dependent_task: dep_task,
        };

        if ($scope.attachment_file != "None") {
          // data = {
          //   user_id: $scope.user_info.user_id,
          //   project_id: project_id_.trim(),
          //   priority: priority_,
          //   task_name: task_.trim(),
          //   client_id: client_id_.trim(),
          //   assigned_to: developer_.trim(),
          //   assigned_by: $scope.user_id.trim(),
          //   t_start_date: start_date_,
          //   t_end_date: end_date_,
          //   p_start_date: p_start_date,
          //   p_end_date: p_end_date,
          //   risk: risk_,
          //   fileName: $scope.attachment_file,
          //   dependent_task: dep_task,
          // };
          data.fileName = $scope.attachment_file;
        }
        console.log(data);
        // return false;
        $scope.upload();
        sendRequest();
      }
    };

    $scope.removeCompletedTasks = function (tasks) {
      console.log("in the tasks");
      console.log(tasks.filter((task) => parseInt(task.completion) !== 100));
      return tasks.filter((task) => parseInt(task.completion) !== 100);
    };

    $scope.getDepartmentMembers = function (users, departmentId) {
      console.log("new members", users);
      console.log("department id ", departmentId);

      return users.filter(
        (user) => parseInt(user.department_id, 10) == parseInt(departmentId, 10)
      );
    };

    $("#departmentElement").change(function () {
      var value = $(this).val();
      console.log("new value", value.split(":")[1]);
      var deptId = value.split(":")[1];
      console.log();
      $scope.departmentMembers = $scope.getDepartmentMembers(
        $scope.all_users,
        deptId
      );
      console.log("new memebers", $scope.departmentMembers);
    });
    

    $("#statusSel").change(function () {
      var value = $(this).val();
      console.log("new value", value.split(":")[1]);
      var statId = value.split(":")[1];
      var data = $scope.project.tasks;
      $scope.tasks = $scope.filterChange(data, statId);
      
    });

    $scope.filterChange = function(data, valueOfFilter){
        var filtered = data.filter((task)=>{
          return parseInt(task.status_id, 10)== parseInt(valueOfFilter, 10)})
        return filtered;
    }

    
    
  }
);
