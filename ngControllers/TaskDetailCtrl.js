sheetApp.filter("myDateFormat", function myDateFormat($filter) {
  return function (text) {
    var tempdate = new Date(text.replace(/-/g, "/"));
    return $filter("date")(tempdate, "EEEE, MMMM d, y @ h:mma");
  };
});
sheetApp.filter("myDateShortFormat", function myDateFormat($filter) {
  return function (text) {
    var tempdate = new Date(text.replace(/-/g, "/"));
    return $filter("date")(tempdate, "EEEE, MMMM d");
  };
});

sheetApp.controller(
  "TaskDetailCtrl",
  function (
    $scope,
    $http,
    $routeParams,
    check_auth,
    myConfig,
    $location,
    $localStorage,
    $timeout,
  ) {
    // $scope.task_id = atob($routeParams.task_id);
    $scope.task_id = $routeParams.task_id;
    console.log($scope.task_id);
    $scope.myConfig_file_url = myConfig.file_url;

    check_auth.verify_auth($localStorage.user_info);
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data;
    console.log("we are here for is department head?");
    console.log($scope.user_info);

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic;
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true;
    console.log($scope.profile_pic);

    //logout funtion
    $scope.logout = function () {
      check_auth.logout();
    };
    // end logout function

    $scope.display_approved_btn = false;

    $scope.get_all_attachments = function () {
      $http({
        method: "GET",
        url:
          myConfig.url +
          "/getAttachFileOfTaskComment.php?task_id=" +
          $scope.task_id,
      }).then(
        function successCallback(response) {
          $scope.all_attachment_file = response.data;
          console.log($scope.all_attachment_file);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        },
      );
    };
    $scope.get_all_attachments();

    //hide the loading button until a chat is submitted
    $("#loadingBtn").hide();

    $scope.get_one_task = function () {
      $http({
        method: "GET",
        url: myConfig.url + "/getOnetask.php?task_id=" + $scope.task_id,
      }).then(
        function successCallback(response) {
          var oneTask = response.data["data"];

          if (!oneTask) {
            return $location.path("/404");
          }
          $scope.project = oneTask[0];
          $scope.oneTask = oneTask[0];
          console.log($scope.oneTask, "the task this");

          // $scope.hide_when_status_is_not_started = false

          if ($scope.oneTask.status == "not started") {
            $scope.hide_when_status_is_not_started = true;
          }

          // $scope.attachment_files = $scope.oneTask['attachment_file']

          var p_start_date = Date.parse($scope.p_start_date);
          var p_end_date = Date.parse($scope.p_end_date);
          console.log($scope.oneTask);

          console.log($scope.project.task_start_date);
          console.log($scope.project.task_end_date);
          var task_start_date = new Date($scope.project.task_start_date);
          var task_end_date = new Date($scope.project.task_end_date);
          var today_date = new Date();
          // Reset time to midnight for consistent date comparison
          today_date.setHours(0, 0, 0, 0);
          task_end_date.setHours(0, 0, 0, 0);
          task_start_date.setHours(0, 0, 0, 0);

          $scope.get_get_actual_no_days_in_milliseconds =
            task_end_date.getTime() - today_date.getTime();
          $scope.number_of_actual_days_till_end =
            $scope.get_get_actual_no_days_in_milliseconds /
            (1000 * 60 * 60 * 24);

          $scope.get_total_task_date_in_milliseconds =
            task_end_date.getTime() - task_start_date.getTime();
          $scope.number_of_days_till_end =
            $scope.get_total_task_date_in_milliseconds / (1000 * 60 * 60 * 24);

          $scope.get_spent_task_date_in_milliseconds =
            today_date.getTime() - task_start_date.getTime();
          $scope.number_of_days_till_today =
            $scope.get_spent_task_date_in_milliseconds / (1000 * 60 * 60 * 24);

          if ($scope.number_of_days_till_today < 0) {
            $scope.progress_bar_percentage = 100;
            $scope.progress_bar_percentage_color = "danger";
            console.log("Days has not started yet");
          } else if (
            $scope.number_of_days_till_today > 0 &&
            $scope.number_of_days_till_today < $scope.number_of_days_till_end
          ) {
            $scope.progress_bar_percentage =
              ($scope.number_of_days_till_today /
                $scope.number_of_days_till_end) *
              100;

            if ($scope.progress_bar_percentage >= 90) {
              $scope.progress_bar_percentage_color = "info";
            } else if ($scope.progress_bar_percentage >= 60) {
              $scope.progress_bar_percentage_color = "info";
            } else if ($scope.progress_bar_percentage >= 40) {
              $scope.progress_bar_percentage_color = "info";
            } else if ($scope.progress_bar_percentage >= 0) {
              $scope.progress_bar_percentage_color = "info";
            } else {
              $scope.progress_bar_percentage_color = "info";
            }

            //    console.log($scope.progress_bar_percentage)
            console.log(
              "progress bar percentage: " + $scope.progress_bar_percentage,
            );
            // console.log('$scope.number_of_days_till_today: ' + $scope.number_of_days_till_today)
            // console.log('$scope.number_of_days_till_end: ' + $scope.number_of_days_till_end)
          } else if (
            $scope.number_of_days_till_today > 0 &&
            $scope.number_of_days_till_today > $scope.number_of_days_till_end
          ) {
            $scope.progress_bar_percentage = 100;
            $scope.progress_bar_percentage_color = "danger";

            //    console.log($scope.progress_bar_percentage)
            console.log("It is pass due date dates");
          }

          // Force green (success) for completed tasks regardless of deadline
          if ($scope.oneTask.status == '61' || $scope.oneTask.status == 'Completed' || $scope.oneTask.status == 'completed' || $scope.percentage_completion >= 100) {
            $scope.progress_bar_percentage = 100;
            $scope.progress_bar_percentage_color = "success";
          }

          // if ((get_spent_task_date_in_milliseconds > get_total_task_date_in_milliseconds) >) {
          //    console.log( $scope.progress_bar_percentage = 100)
          // }else{
          //     console.log($scope.progress_bar_percentage = ($scope.number_of_days_till_today / get_total_task_date_in_milliseconds) * 100 )

          // }

          console.log($scope.number_of_days_till_today);
          $scope.percentage_completion = $scope.oneTask.completion;
          console.log($scope.percentage_completion);

          console.log($scope.percentage_completion);
          if ($scope.percentage_completion >= 100) {
            $scope.percentage_color = "success";
          } else if ($scope.percentage_completion >= 60) {
            $scope.percentage_color = "info";
          } else if ($scope.percentage_completion >= 40) {
            $scope.percentage_color = "primary";
          } else if ($scope.percentage_completion >= 20) {
            $scope.percentage_color = "warning";
          } else {
            $scope.percentage_color = "danger";
          }

          // DEFINING PRIVILLEGES AND ACCESS
          if (
            $scope.user_info.is_dept_head == "1" &&
            $scope.user_info.department_id == $scope.project.department_id
          ) {
            $scope.department_head_can_see = true;
          }
          if ($scope.user_info.user_id == $scope.project.assigned_to_id) {
            $scope.developer_can_see = true;
          }
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        },
      );
    };
    $scope.get_one_task();

    //code to get updates on tasks
    $scope.get_task_logs = function () {
      $http({
        method: "GET",
        url: myConfig.url + "/getAllTaskUpdates.php?task_id=" + $scope.task_id,
      }).then(
        function successCallback(response) {
          var taskUpdates = response.data["data"];
          console.log("first try ");
          console.log(taskUpdates);
          $scope.taskUpdates = taskUpdates;
          console.log($scope.taskUpdates);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        },
      );
    };

    $scope.get_task_logs();

    $scope.get_task_comments = function () {
      $http({
        method: "GET",
        url: myConfig.url + "/getTaskComment.php?task_id=" + $scope.task_id,
      }).then(
        function successCallback(response) {
          $scope.comments = response.data;

          console.log($scope.comments);
        },
        function errorCallback(response) {
          // alert("Error. Try Again!");
        },
      );
    };
    $scope.get_task_comments();

    setInterval($scope.get_task_comments(), 100000);

    // ===== Schedule meeting support (copied/adapted from TaskCtrl) =====
    $scope.showScheduleModal = false;
    $scope.selectedMembers = [];
    $scope.selectedMemberDropdown = "";
    $scope.selectedDepartmentDropdown = "";
    $scope.meetingForm = {};
    $scope.meetingFormLoading = false;
    $scope.allUsers = [];
    $scope.allDepartments = [];

    $scope.showAlert = function (message, type) {
      type = type || "success";
      var alertDiv = document.getElementById("alertContainer");
      if (!alertDiv) {
        alertDiv = document.createElement("div");
        alertDiv.id = "alertContainer";
        alertDiv.className = "alert-container";
        document.body.appendChild(alertDiv);
      }
      var alert = document.createElement("div");
      alert.className = "custom-alert";
      alert.innerHTML =
        '<span class="alert-message">' +
        message +
        '</span><span class="alert-close">&times;</span>';
      alertDiv.appendChild(alert);
      alert
        .querySelector(".alert-close")
        .addEventListener("click", function () {
          alert.style.animation = "slideOut 0.3s ease forwards";
          setTimeout(() => alert.remove(), 300);
        });
      setTimeout(() => {
        alert.style.animation = "slideOut 0.3s ease forwards";
        setTimeout(() => alert.remove(), 300);
      }, 4000);
    };

    $scope.loadAllUsers = function () {
      $http.get("apiSheet/getAllUsers.php").then(function (response) {
        if (Array.isArray(response.data)) {
          $scope.allUsers = response.data.map(function (user) {
            return {
              id: user.user_id || user.id,
              f_name: user.f_name,
              l_name: user.l_name,
              dept: user.department || user.dept,
              email: user.email,
              is_active: user.is_active,
            };
          });
          var deptSet = {};
          $scope.allUsers.forEach(function (user) {
            if (user.dept && user.is_active) deptSet[user.dept] = true;
          });
          $scope.allDepartments = Object.keys(deptSet).sort();
        }
      });
    };

    $scope.openScheduleModal = function (event) {
      if (event) event.stopPropagation();
      $scope.loadAllUsers();
      $scope.showScheduleModal = true;
      $scope.selectedMembers = [];
      $scope.selectedMemberDropdown = "";
      $scope.selectedDepartmentDropdown = "";

      var taskTitle =
        $scope.oneTask && $scope.oneTask.task_description
          ? $scope.oneTask.task_description
          : "Task Meeting";
      var taskDescription =
        $scope.oneTask &&
        $scope.oneTask.task_id &&
        $scope.oneTask.task_description
          ? "REF-0000" +
            $scope.oneTask.task_id +
            " - " +
            $scope.oneTask.task_description
          : "";
      $scope.meetingForm = {
        date: new Date(),
        time: "",
        title: taskTitle,
        description: taskDescription,
      };
      $scope.meetingFormLoading = false;
      $timeout(function () {
        var modal = angular.element(
          document.querySelector(".schedule-modal-backdrop"),
        );
        if (modal.length) modal.addClass("show");
      }, 50);
    };

    $scope.closeScheduleModal = function () {
      var modal = angular.element(
        document.querySelector(".schedule-modal-backdrop"),
      );
      if (modal.length) modal.removeClass("show");
      $timeout(function () {
        $scope.showScheduleModal = false;
      }, 300);
    };

    $scope.addMemberToSchedule = function () {
      if (!$scope.selectedMemberDropdown) {
        $scope.showAlert("Please select a member", "error");
        return;
      }
      var userId = $scope.selectedMemberDropdown;
      if (
        $scope.selectedMembers.some(function (m) {
          return String(m.id) === String(userId);
        })
      ) {
        $scope.showAlert("This member is already selected", "error");
        return;
      }
      var user = $scope.allUsers.find(function (u) {
        return String(u.id) === String(userId);
      });
      if (user) {
        $scope.selectedMembers.push(user);
        $scope.selectedMemberDropdown = "";
        $scope.showAlert(user.f_name + " added to meeting", "success");
      } else {
        $scope.showAlert("Member not found", "error");
      }
    };

    $scope.removeMemberFromSchedule = function (userId) {
      $scope.selectedMembers = $scope.selectedMembers.filter(function (m) {
        return m.id !== userId;
      });
      $scope.showAlert("Member removed", "success");
    };

    $scope.addDepartmentMembersToSchedule = function () {
      if (!$scope.selectedDepartmentDropdown) {
        $scope.showAlert("Please select a department", "error");
        return;
      }
      var selectedDept = $scope.selectedDepartmentDropdown;
      var deptMembers = $scope.allUsers.filter(function (user) {
        return user.dept === selectedDept && user.is_active;
      });
      if (deptMembers.length === 0) {
        $scope.showAlert("No active members found in " + selectedDept, "error");
        return;
      }
      var addedCount = 0;
      deptMembers.forEach(function (member) {
        if (
          !$scope.selectedMembers.some(function (m) {
            return m.id === member.id;
          })
        ) {
          $scope.selectedMembers.push(member);
          addedCount++;
        }
      });
      $scope.selectedDepartmentDropdown = "";
      $scope.showAlert(
        "Added " + addedCount + " members from " + selectedDept,
        "success",
      );
    };

    $scope.submitMeetingForm = function () {
      if (
        !$scope.meetingForm.title ||
        !$scope.meetingForm.date ||
        !$scope.meetingForm.time ||
        $scope.selectedMembers.length === 0
      ) {
        $scope.showAlert(
          "Please fill in all required fields and select at least one member",
          "error",
        );
        return;
      }
      var meetingDate = new Date($scope.meetingForm.date);
      var dateString =
        meetingDate.getFullYear() +
        "-" +
        String(meetingDate.getMonth() + 1).padStart(2, "0") +
        "-" +
        String(meetingDate.getDate()).padStart(2, "0");
      var timeStr = $scope.meetingForm.time;
      if (timeStr instanceof Date) {
        timeStr =
          String(timeStr.getHours()).padStart(2, "0") +
          ":" +
          String(timeStr.getMinutes()).padStart(2, "0");
      } else if (typeof timeStr === "string" && timeStr.length > 5) {
        timeStr = timeStr.substring(0, 5);
      }
      if (!timeStr || !/^\d{2}:\d{2}$/.test(timeStr)) {
        $scope.showAlert("Time must be in HH:MM format", "error");
        return;
      }
      $scope.meetingFormLoading = true;
      $scope.meetingForm.time = timeStr;
      var meetingData = $scope.collectMeetingData(timeStr, dateString);
      $scope.saveMeetingSchedule(meetingData);
    };

    $scope.collectMeetingData = function (timeStr, dateString) {
      var participantIds = $scope.selectedMembers
        .map(function (m) {
          return m.id;
        })
        .join(",");
      return {
        title: $scope.meetingForm.title,
        description: $scope.meetingForm.description || "",
        scheduled_date: dateString,
        scheduled_time: timeStr,
        participants: participantIds,
        created_by: $scope.user_info.user_id,
        reference_type: "task",
        reference_id:
          $scope.oneTask && $scope.oneTask.task_id
            ? parseInt($scope.oneTask.task_id)
            : null,
      };
    };

    $scope.saveMeetingSchedule = function (meetingData) {
      var requestUrl =
        myConfig.url + "/schedule.php?action=save_scheduled_meeting";
      $http({
        method: "POST",
        url: requestUrl,
        data: meetingData,
        headers: { "Content-Type": "application/json" },
      }).then(
        function (response) {
          $scope.meetingFormLoading = false;
          if (
            response.data &&
            (response.data.status === "success" || response.data.success)
          ) {
            $scope.showAlert("Meeting scheduled successfully!", "success");
            $scope.closeScheduleModal();
          } else {
            $scope.showAlert(
              response.data.message || "Failed to schedule meeting",
              "error",
            );
          }
        },
        function (error) {
          $scope.meetingFormLoading = false;
          $scope.showAlert("Error scheduling meeting", "error");
          console.error(error);
        },
      );
    };

    // ATTACHMENT ICON FUNCTION

    $scope.attachment_file = "None";
    $scope.get_file_input = function () {
      // $("#upload_file").click(function () {
      //     $("#id_file_field").trigger('click');
      // });
      $("#attached-file").change(function () {
        var value = this.value;
        var fileName =
          typeof value == "string" ? value.match(/[^\/\\]+$/)[0] : value[0];
        // console.log(fileName);
        $scope.attachment_file = fileName;
        $("#show_attach_name").text(fileName);
      });
    };
    // END ATTACHMENT ICON FUNCTION

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
        $("#show_attach_name").text("None");
        $scope.attachment_file = "None";
      });
    };

    $scope.createComment = function (comment_message, data) {
      if (comment_message == "" || comment_message == undefined) {
      } else {
        //  console.log(data);
        //$http POST function

        $scope.upload();

        $http({
          method: "POST",
          url: myConfig.url + "/createComment.php",
          data: data,
        }).then(
          function successCallback(response) {
            $res = response.data;
            console.log($res);
            if ($res.status == "success") {
              $("#comment_input_form")[0].reset();

              $scope.get_task_comments();
              $scope.get_all_attachments();
              $("#sendBtn").show();
              $("#loadingBtn").hide();
            } else {
            }
          },
          function errorCallback(response) {},
        );
      }
    };

    // $("#prgIcon").hide()

    $scope.sendComment = function (task_id, comment_message, user_id) {
      // console.log(task_id);
      // console.log(comment_message);
      // console.log(attach);
      // console.log(user_id);
      $("#sendBtn").hide();
      $("#loadingBtn").show();

      var dept_id = $scope.user_info.department_id;
      var is_dept_head_ = $scope.user_info.is_dept_head;
      console.log(" this is the current department ");
      console.log(dept_id);

      // var data = {
      //     comment: comment_message,
      //     task_id: task_id,
      //     attach: null,
      //     posted_by: user_id
      // }
      // console.log(data)

      if ($scope.attachment_file == "None") {
        var data = {
          comment: comment_message,
          task_id: task_id,
          attach: null,
          posted_by: $scope.user_info.user_id,
          department_id: dept_id,
          is_dept_head: is_dept_head_,
          taskOwnerId: $scope.oneTask.assigned_to_id,
          project_name: $scope.project.project_name,
          task_name: $scope.oneTask.task_description,
          status: $scope.oneTask.status,
        };
        // console.log(data); return false;
        $scope.createComment(comment_message, data);
      } else {
        var data = {
          comment: comment_message,
          task_id: task_id,
          attach: $scope.attachment_file,
          posted_by: $scope.user_info.user_id,
          department_id: dept_id,
          is_dept_head: is_dept_head_,
          taskOwnerId: $scope.oneTask.assigned_to_id,
          project_name: $scope.project.project_name,
          task_name: $scope.oneTask.task_description,
          status: $scope.oneTask.status,
        };
        $scope.createComment(comment_message, data);
      }
    };

    $scope.open_modal_comment = function (comment) {
      console.log(comment);
      $scope.comment_for_reply = comment;
      $scope.comment_id_for_reply = comment.comment_id;
      $("#display_reply_comment").text(comment.comment);
    };

    $scope.open_delete_modal_comment = function (comment) {
      console.log(comment);
      $scope.comment_for_delete = comment;
      $scope.comment_id_for_delete = comment.comment_id;
      $("#display_delete_comment").text(comment.comment);
    };

    $scope.replyComment = function (
      comment_id_for_reply,
      reply_message,
      user_id,
    ) {
      console.log(comment_id_for_reply);
      console.log(reply_message);

      var data = {
        reply: reply_message,
        comment_id: comment_id_for_reply,
        replied_by: user_id,
      };

      if (reply_message == "" || reply_message == undefined) {
      } else {
        //  console.log(data);
        //$http POST function
        $http({
          method: "POST",
          url: myConfig.url + "/createReply.php",
          data: data,
        }).then(
          function successCallback(response) {
            $res = response.data;
            console.log($res);
            if ($res.status == "success") {
              //   $('#task_comment_input').val('');
              $scope.get_task_comments();
              $("#reply_input_form")[0].reset();
              $("#modal-reply-comment").hide();
              $(".modal-backdrop").hide();
            } else {
            }
          },
          function errorCallback(response) {},
        );
      }
    };

    $scope.deleteComment = function (comment_id_for_delete) {
      console.log("This is it: ");
      console.log(comment_id_for_delete);
      // console.log("stop the program...");
      // return false;

      var data = {
        comment_id: comment_id_for_delete,
      };

      $http({
        method: "POST",
        url: myConfig.url + "/deleteComment.php",
        data: data,
      }).then(
        function successCallback(response) {
          $res = response.data;
          console.log($res);
          if ($res.status == "success") {
            //   $('#task_comment_input').val('');
            $scope.get_task_comments();
            $("#reply_input_form")[0].reset();
            $("#modal-delete-comment").hide();
            $(".modal-backdrop").hide();
          } else {
          }

          window.location.reload();
        },
        function errorCallback(response) {},
      );
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
          console.log("error");
        },
      );
    };
    $scope.get_code_desc("sta");

    $scope.saveTaskUpdate = function (
      taskStatus,
      ready_for_test,
      percentage_completion,
    ) {
      console.log(taskStatus);
      console.log(ready_for_test);
      console.log(percentage_completion);
      // return false;

      if (ready_for_test == undefined || ready_for_test == "") {
        var ready_for_test = false;
      }

      if (taskStatus == "" || taskStatus == undefined) {
        var taskStatus = $scope.oneTask.status_id;
        // Swal.fire({
        //     type: 'error',
        //     title: $scope.oneTask.status_id
        // })
      }
      if (
        (taskStatus == "58" || taskStatus == "" || taskStatus == undefined) &&
        percentage_completion > 0
      ) {
        Swal.fire({
          type: "error",
          title: "Check status",
          text: 'Change task status from "Not Started"',
        });
      } else {
        //code to create a date object for date of completion
        let completionDate = null;

        //code for project id
        let project_id = $scope.project_id;

        if (taskStatus == "61") {
          percentage_completion = 100;
          completionDate = new Date();
          console.log(completionDate);
        }

        if (percentage_completion == 100) {
          taskStatus = "61";
          completionDate = new Date();
          console.log(completionDate);
        }
        var data = {
          user_id: $scope.user_info.user_id,
          task_id: $scope.task_id,
          taskStatus: taskStatus,
          ready_for_test: ready_for_test,
          percentage_completion: percentage_completion,
          completionDate: completionDate,
          project_id: $scope.project.project_id,
        };
        console.log(data);
        // return false;
        $scope.sendTaskUpdate(data);

        if (ready_for_test == undefined || ready_for_test == "") {
          var ready_for_test = false;
        } else {
        }
      }

      // console.log(data)
    };

    $scope.sendTaskUpdate = function (data) {
      var data = data;
      $http({
        method: "POST",
        url: myConfig.url + "/updateTaskByDeveloper.php",
        data: data,
      }).then(
        function successCallback(response) {
          $res = response.data;
          console.log($res);
          if ($res.status == "success") {
            // $scope.get_one_task()

            Swal.fire({
              type: "success",
              title: "REF-0000" + $res.task_id,
              text: $res.message,
            });

            setTimeout(function () {
              location.reload();
            }, 700);

            var comment_message =
              "<span class='text-success'> New Updates: <br> [Task Status - >  <br> Go For Test - > " +
              data.ready_for_test +
              " <br> Task Completion - > " +
              data.percentage_completion +
              "] </span>";

            var data = {
              comment: comment_message,
              task_id: $scope.task_id,
              attach: null,
              posted_by: $scope.user_info.user_id,
            };
            // $scope.createComment(comment_message, data)
            console.log(data);

            $scope.get_one_task();
          } else {
            Swal.fire({
              type: "error",
              title: $res.status,
              text: $res.message,
            });
          }
        },
        function errorCallback(response) {},
      );
    };

    $scope.sendForTest = function (data) {};
  },
);
