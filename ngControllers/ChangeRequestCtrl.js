angular.module('sheetApp').controller('ChangeRequestCtrl', ['$scope', '$http', '$timeout', '$location', '$localStorage', 'check_auth', 'myConfig', function($scope, $http, $timeout, $location, $localStorage, check_auth, myConfig) {
    // Initialize variables
    $scope.user_info = {};
    $scope.isApprover = false;
    $scope.profile_pic_true = false;
    $scope.profile_pic = '';
    $scope.showSuccessModal = false;
    $scope.isSubmitting = false;
    $scope.referenceData = {};
    $scope.clients = [];
    $scope.users = [];
    $scope.countries = [];
    $scope.projects = [];
    $scope.tickets = [];
    $scope.filteredProjectsAndTickets = [];
    $scope.showProjectOrTicketSuggestions = false;
    $scope.requests = [];
    $scope.filteredRequests = [];
    $scope.currentFilter = 'all';
    $scope.filterDisplayText = 'All';
    $scope.selectedRequest = {};
    $scope.isImplementationSaved = false;
    $scope.allApprovalsComplete = false;
    $scope.approval = {
        deptHead: { name: '', date: null, dateFormatted: '', comments: '', approved: false },
        qa: { name: '', date: null, dateFormatted: '', comments: '', approved: false },
        ceo: { name: '', date: null, dateFormatted: '', comments: '', approved: false }
    };
    $scope.newRequest = {
        submitted_by: null,
        requestBy: null,
        implementer: null,
        projectId: null,
        ticketId: null,
        projectOrTicketIdFormatted: ''
    };
    $scope.showClientLogsButton = false;
    const allowedUserIds = [125, 138, 144, 145, 147, 153, 196];

    // Helper function to format date to YYYY-MM-DD
    $scope.formatDateForInput = function(date) {
        if (!date) return '';
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    // Load user info
    $scope.loadUserInfo = function() {
        try {
            console.log('localStorage.user_info:', $localStorage.user_info);
            if (!$localStorage.user_info || !$localStorage.user_info.data || !$localStorage.user_info.data.user_id) {
                console.error('Invalid or missing user info in localStorage, attempting API fetch');
                $http.get('apiSheet/change_request.php?action=get_user_info')
                    .then(function(response) {
                        console.log('get_user_info response:', response.data);
                        if (response.data.success && response.data.data && response.data.data.user_id) {
                            $localStorage.user_info = { data: response.data.data };
                            $scope.user_info = $localStorage.user_info.data;
                            console.log('API user info loaded:', $scope.user_info);
                            initializeUserData();
                        } else {
                            console.error('Failed to load user info from API:', response.data.message || 'No message');
                            check_auth.logout();
                            window.location.href = '/login';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error fetching user info from API:', error);
                        check_auth.logout();
                        window.location.href = '/login';
                    });
                return;
            }

            check_auth.verify_auth($localStorage.user_info);
            $scope.user_info = $localStorage.user_info.data || {};
            console.log('User info from localStorage:', $scope.user_info);

            if (!$scope.user_info.user_id || isNaN($scope.user_info.user_id)) {
                console.error('User ID is missing or invalid in localStorage, attempting API fetch');
                $http.get('apiSheet/change_request.php?action=get_user_info')
                    .then(function(response) {
                        console.log('get_user_info response:', response.data);
                        if (response.data.success && response.data.data && response.data.data.user_id) {
                            $localStorage.user_info = { data: response.data.data };
                            $scope.user_info = $localStorage.user_info.data;
                            console.log('API user info loaded:', $scope.user_info);
                            initializeUserData();
                        } else {
                            console.error('Failed to load user info from API:', response.data.message || 'No message');
                            check_auth.logout();
                            window.location.href = '/login';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error fetching user info from API:', error);
                        check_auth.logout();
                        window.location.href = '/login';
                    });
                return;
            }

            initializeUserData();
        } catch (e) {
            console.error('Error in loadUserInfo:', e);
            check_auth.logout();
            window.location.href = '/login';
        }
    };

    // Helper function to initialize user-related data
    function initializeUserData() {
        $scope.isApprover = $scope.user_info.can_approve == 1;
        console.log('isApprover set to:', $scope.isApprover);

        if ($scope.user_info.profile_pic) {
            $scope.profile_pic_true = true;
            $scope.profile_pic = myConfig.file_url + $scope.user_info.profile_pic;
        }

        $scope.newRequest.submitted_by = $scope.user_info.user_id;
        $scope.newRequest.requestBy = $scope.user_info.user_id;
        $scope.newRequest.implementer = $scope.user_info.user_id;
        console.log('newRequest initialized:', $scope.newRequest);
        $scope.showClientLogsButton = allowedUserIds.includes(parseInt($scope.user_info.user_id));
        console.log('showClientLogsButton set to:', $scope.showClientLogsButton);
    }

    // Check if the logged-in user is eligible to approve at a specific level
    $scope.isUserEligible = function(approvalLevel) {
        if (!$scope.user_info || !$scope.user_info.user_id || !$scope.selectedRequest || !$scope.selectedRequest.approval_status) {
            return false;
        }

        // Check if user has already approved at another level
        var hasApprovedOtherLevel = false;
        var currentUserId = $scope.user_info.user_id;
        var approvalStatus = $scope.selectedRequest.approval_status;

        if (approvalLevel !== 'dept_head' && approvalStatus.dept_head.comments && approvalStatus.dept_head.name === ($scope.user_info.first_name + ' ' + $scope.user_info.last_name)) {
            hasApprovedOtherLevel = true;
        }
        if (approvalLevel !== 'qa' && approvalStatus.qa.comments && approvalStatus.qa.name === ($scope.user_info.first_name + ' ' + $scope.user_info.last_name)) {
            hasApprovedOtherLevel = true;
        }
        if (approvalLevel !== 'ceo' && approvalStatus.ceo.comments && approvalStatus.ceo.name === ($scope.user_info.first_name + ' ' + $scope.user_info.last_name)) {
            hasApprovedOtherLevel = true;
        }

        if (hasApprovedOtherLevel) {
            return false;
        }

        if (approvalLevel === 'dept_head') {
            return $scope.user_info.is_dept_head == 1 && $scope.user_info.can_approve == 1;
        }
        if (!$scope.referenceData.approvers || !$scope.referenceData.approvers[approvalLevel]) {
            return false;
        }
        return $scope.referenceData.approvers[approvalLevel].some(function(person) {
            return person.id == $scope.user_info.user_id;
        });
    };

    // Set default approver names and current date based on logged-in user
    $scope.setDefaultApproverNames = function() {
        if ($scope.user_info.user_id) {
            var userFullName = $scope.user_info.first_name + ' ' + $scope.user_info.last_name;
            var currentDate = new Date();
            var formattedCurrentDate = $scope.formatDateForInput(currentDate);

            if ($scope.isUserEligible('dept_head') && !$scope.approval.deptHead.comments) {
                $scope.approval.deptHead.name = userFullName;
                $scope.approval.deptHead.date = currentDate;
                $scope.approval.deptHead.dateFormatted = formattedCurrentDate;
            }

            if ($scope.isUserEligible('qa') && !$scope.approval.qa.comments) {
                $scope.approval.qa.name = userFullName;
                $scope.approval.qa.date = currentDate;
                $scope.approval.qa.dateFormatted = formattedCurrentDate;
            }

            if ($scope.isUserEligible('ceo') && !$scope.approval.ceo.comments) {
                $scope.approval.ceo.name = userFullName;
                $scope.approval.ceo.date = currentDate;
                $scope.approval.ceo.dateFormatted = formattedCurrentDate;
            }
        }
    };

    // Check if all approval stages are complete
    $scope.checkAllApprovalsComplete = function() {
        $scope.allApprovalsComplete = $scope.approval.deptHead.comments &&
            $scope.approval.qa.comments &&
            $scope.approval.ceo.comments;
    };

    // Load users for dropdown
    $scope.loadUsers = function() {
        $http.get('apiSheet/change_request.php?action=get_users')
            .then(function(response) {
                if (response.data.success) {
                    $scope.users = response.data.data.map(function(user) {
                        return {
                            id: String(user.user_id || user.id),
                            name: user.name || `${user.first_name} ${user.last_name}`
                        };
                    });
                    console.log('Users loaded:', $scope.users);
                    if ($scope.user_info.user_id && !$scope.newRequest.requestBy) {
                        $scope.newRequest.requestBy = String($scope.user_info.user_id);
                        $scope.newRequest.implementer = String($scope.user_info.user_id);
                        console.log('requestBy and implementer set after users loaded:', $scope.newRequest.requestBy, $scope.newRequest.implementer);
                    }
                    const userExists = $scope.users.some(function(user) {
                        return user.id === $scope.user_info.user_id;
                    });
                    console.log('Logged-in user exists in users list:', userExists);
                    $timeout(function() {
                        $scope.$apply();
                    });
                } else {
                    console.error('Failed to load users:', response.data.message);
                    if ($scope.referenceData.users) {
                        $scope.users = $scope.referenceData.users.map(function(user) {
                            return {
                                id: String(user.user_id || user.id),
                                name: user.name || `${user.first_name} ${user.last_name}`
                            };
                        });
                    }
                }
            })
            .catch(function(error) {
                console.error('Error loading users:', error);
                if ($scope.referenceData.users) {
                    $scope.users = $scope.referenceData.users.map(function(user) {
                        return {
                            id: String(user.user_id || user.id),
                            name: user.name || `${user.first_name} ${user.last_name}`
                        };
                    });
                }
            });
    };

    // Logout function
    $scope.logout = function() {
        check_auth.logout($scope.user_info.user_id);
    };

    // Format time from HH:MM AM/PM to database format (HH:MM:SS)
    $scope.formatTimeForDatabase = function(hours, minutes, period) {
        if (!hours || !minutes || !period) return null;
        hours = parseInt(hours);
        minutes = parseInt(minutes);
        if (period === 'PM' && hours < 12) hours += 12;
        if (period === 'AM' && hours === 12) hours = 0;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
    };

    // Parse database time (HH:MM:SS) to display format
    $scope.parseDatabaseTime = function(time) {
        if (!time) return { hours: null, minutes: null, period: 'AM' };
        const [hours, minutes] = time.split(':').map(Number);
        const period = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 === 0 ? 12 : hours % 12;
        return {
            hours: displayHours,
            minutes: minutes,
            period: period
        };
    };

    // Load reference data for dropdowns
    $scope.loadReferenceData = function() {
        $http.get('apiSheet/change_request.php?action=get_reference_data')
            .then(function(response) {
                if (response.data.success) {
                    $scope.referenceData = response.data.data;
                    console.log('Reference data loaded:', $scope.referenceData);
                    $scope.setDefaultApproverNames();
                    $scope.checkAllApprovalsComplete();
                } else {
                    console.error('Failed to load reference data:', response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error loading reference data:', error);
            });
    };

    // Load clients
    $scope.loadClients = function() {
        $http.get('https://issues.unionsg.com/js/getClients.php')
            .then(function(response) {
                console.log('Clients API response:', response.data);
                $scope.clients = response.data
                    .filter(function(client) {
                        return client.is_active === "1" || client.is_active === 1;
                    })
                    .map(function(client) {
                        return {
                            id: client.Company_Id,
                            name: client.Company_Name,
                            country: client.Country
                        };
                    });
                console.log('Clients loaded from external API:', $scope.clients);
            })
            .catch(function(error) {
                console.error('Error loading clients from external API:', error);
                $http.get('apiSheet/change_request.php?action=get_clients')
                    .then(function(fallbackResponse) {
                        if (fallbackResponse.data.success) {
                            $scope.clients = fallbackResponse.data.data;
                        }
                    });
            });
    };

    // Auto-populate country on client selection
    $scope.selectClient = function() {
        var selectedClient = $scope.clients.find(function(client) {
            return client.id == $scope.newRequest.clientAffected;
        });
        if (selectedClient) {
            $scope.newRequest.clientAffected = selectedClient.id;
            $scope.newRequest.clientName = selectedClient.name;
            $scope.newRequest.country = selectedClient.country || selectedClient.Country || '';
            console.log('Selected client:', selectedClient);
        } else {
            $scope.newRequest.clientAffected = '';
            $scope.newRequest.clientName = '';
            $scope.newRequest.country = '';
        }
    };

    // Load projects
    $scope.loadProjects = function() {
        $http.get('apiSheet/change_request.php?action=get_projects')
            .then(function(response) {
                if (response.data.success) {
                    $scope.projects = response.data.data.map(function(project) {
                        return {
                            id: parseInt(project.id),
                            formatted_id: 'PROJ-' + String(project.id).padStart(8, '0'),
                            type: 'project'
                        };
                    });
                    console.log('Projects loaded:', $scope.projects);
                } else {
                    console.error('Failed to load projects:', response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error loading projects:', error);
            });
    };

    // Load tickets
    $scope.loadTickets = function() {
        $http.get('apiSheet/change_request.php?action=get_tickets')
            .then(function(response) {
                if (response.data.success) {
                    $scope.tickets = response.data.data.map(function(ticket) {
                        let numericId = ticket.id;
                        if (typeof ticket.id === 'string' && ticket.id.startsWith('IN')) {
                            numericId = parseInt(ticket.id.replace('IN', ''), 10);
                        }

                        return {
                            id: numericId,
                            formatted_id: ticket.formatted_id,
                            type: 'ticket'
                        };
                    });
                    console.log('Tickets loaded:', $scope.tickets);
                } else {
                    console.error('Failed to load tickets:', response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error loading tickets:', error);
            });
    };

    // Filter projects and tickets based on input
    $scope.filterProjectsAndTickets = function(event) {
        var input = $scope.newRequest.projectOrTicketIdFormatted || '';
        if (input.length >= 3) {
            $scope.filteredProjectsAndTickets = [];
            var filteredProjects = $scope.projects.filter(function(project) {
                return project.formatted_id.toLowerCase().includes(input.toLowerCase());
            });
            var filteredTickets = $scope.tickets.filter(function(ticket) {
                return ticket.formatted_id.toLowerCase().includes(input.toLowerCase());
            });
            $scope.filteredProjectsAndTickets = [...filteredProjects, ...filteredTickets].sort(function(a, b) {
                return a.formatted_id.localeCompare(b.formatted_id);
            });
            $scope.showProjectOrTicketSuggestions = $scope.filteredProjectsAndTickets.length > 0;
        } else {
            $scope.showProjectOrTicketSuggestions = false;
            $scope.filteredProjectsAndTickets = [];
        }
    };

    // Select project or ticket from autocomplete
    $scope.selectProjectOrTicket = function(item) {
        if (item.type === 'project') {
            $scope.newRequest.projectId = item.id;
            $scope.newRequest.ticketId = null;
        } else if (item.type === 'ticket') {
            $scope.newRequest.ticketId = item.id;
            $scope.newRequest.projectId = null;
        }
        $scope.newRequest.projectOrTicketIdFormatted = item.formatted_id;
        $scope.showProjectOrTicketSuggestions = false;
        $scope.filteredProjectsAndTickets = [];
        console.log('Selected item:', item);
    };

    // Load change requests
    $scope.loadRequests = function() {
        $http.get('apiSheet/change_request.php?action=get_requests')
            .then(function(response) {
                if (response.data.success) {
                    $scope.requests = response.data.data;
                    $scope.filterRequests($scope.currentFilter);
                } else {
                    console.error('Failed to load requests:', response.data.message);
                    alert('Failed to load change requests: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error loading requests:', error);
                alert('An error occurred while loading change requests.');
            });
    };

    // Load specific request details based on requestId
    $scope.loadRequestDetails = function() {
        var requestId = $location.search().requestId;
        if (requestId) {
            $http.get('apiSheet/change_request.php?action=get_request&id=' + requestId)
                .then(function(response) {
                    if (response.data.success) {
                        $scope.selectedRequest = response.data.data;
                        $scope.selectedRequest.implementation_start = $scope.selectedRequest.implementation_start || null;
                        $scope.selectedRequest.implementation_end = $scope.selectedRequest.implementation_end || null;
                        const startTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_start);
                        const endTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_end);
                        $scope.selectedRequest.implementation_start_hours = startTime.hours;
                        $scope.selectedRequest.implementation_start_minutes = startTime.minutes;
                        $scope.selectedRequest.implementation_start_period = startTime.period;
                        $scope.selectedRequest.implementation_end_hours = endTime.hours;
                        $scope.selectedRequest.implementation_end_minutes = endTime.minutes;
                        $scope.selectedRequest.implementation_end_period = endTime.period;
                        $scope.isImplementationSaved = $scope.selectedRequest.implementation_status && ['Successful', 'Failed', 'Rescheduled'].includes($scope.selectedRequest.implementation_status);
                        $scope.approval.deptHead = {
                            name: $scope.selectedRequest.approval_status.dept_head.comments ? ($scope.selectedRequest.approval_status.dept_head.name || '') : '',
                            date: $scope.selectedRequest.approval_status.dept_head.comments && $scope.selectedRequest.approval_status.dept_head.date ? new Date($scope.selectedRequest.approval_status.dept_head.date) : null,
                            dateFormatted: $scope.selectedRequest.approval_status.dept_head.comments && $scope.selectedRequest.approval_status.dept_head.date ? $scope.formatDateForInput(new Date($scope.selectedRequest.approval_status.dept_head.date)) : '',
                            comments: $scope.selectedRequest.approval_status.dept_head.comments || '',
                            approved: $scope.selectedRequest.approval_status.dept_head.approved
                        };
                        $scope.approval.qa = {
                            name: $scope.selectedRequest.approval_status.qa.comments ? ($scope.selectedRequest.approval_status.qa.name || '') : '',
                            date: $scope.selectedRequest.approval_status.qa.comments && $scope.selectedRequest.approval_status.qa.date ? new Date($scope.selectedRequest.approval_status.qa.date) : null,
                            dateFormatted: $scope.selectedRequest.approval_status.qa.comments && $scope.selectedRequest.approval_status.qa.date ? $scope.formatDateForInput(new Date($scope.selectedRequest.approval_status.qa.date)) : '',
                            comments: $scope.selectedRequest.approval_status.qa.comments || '',
                            approved: $scope.selectedRequest.approval_status.qa.approved
                        };
                        $scope.approval.ceo = {
                            name: $scope.selectedRequest.approval_status.ceo.comments ? ($scope.selectedRequest.approval_status.ceo.name || '') : '',
                            date: $scope.selectedRequest.approval_status.ceo.comments && $scope.selectedRequest.approval_status.ceo.date ? new Date($scope.selectedRequest.approval_status.ceo.date) : null,
                            dateFormatted: $scope.selectedRequest.approval_status.ceo.comments && $scope.selectedRequest.approval_status.ceo.date ? $scope.formatDateForInput(new Date($scope.selectedRequest.approval_status.ceo.date)) : '',
                            comments: $scope.selectedRequest.approval_status.ceo.comments || '',
                            approved: $scope.selectedRequest.approval_status.ceo.approved
                        };
                        $scope.setDefaultApproverNames();
                        $scope.checkAllApprovalsComplete();
                    } else {
                        console.error('Failed to load request:', response.data.message);
                        alert('Failed to load change request: ' + response.data.message);
                    }
                })
                .catch(function(error) {
                    console.error('Error loading request:', error);
                    alert('An error occurred while loading the change request.');
                });
        }
    };

    // Filter requests based on status
    $scope.filterRequests = function(filter) {
        $scope.currentFilter = filter;
        $scope.filterDisplayText = {
            'all': 'All',
            'my_requests': 'My Requests',
            'my_approved': 'My Approved',
            'pending': 'Pending',
            'approved': 'Approved',
            'rejected': 'Rejected',
            'failed': 'Failed',
            'rescheduled': 'Rescheduled',
            'implemented': 'Implemented'
        }[filter] || 'All';

        if (filter === 'all') {
            $scope.filteredRequests = $scope.requests;
        } else if (filter === 'my_requests') {
            $scope.filteredRequests = $scope.requests.filter(function(request) {
                return request.submitted_by == $scope.user_info.user_id;
            });
        } else if (filter === 'my_approved') {
            $http.get('apiSheet/change_request.php?action=get_requests&filter=my_approved&user_id=' + $scope.user_info.user_id)
                .then(function(response) {
                    if (response.data.success) {
                        $scope.filteredRequests = response.data.data;
                        $timeout(function() {
                            $scope.$apply();
                        });
                    } else {
                        console.error('Failed to load my approved requests:', response.data.message);
                        alert('Failed to load my approved requests: ' + response.data.message);
                        $scope.filteredRequests = [];
                    }
                })
                .catch(function(error) {
                    console.error('Error loading my approved requests:', error);
                    alert('An error occurred while loading my approved requests.');
                    $scope.filteredRequests = [];
                });
        } else {
            $http.get('apiSheet/change_request.php?action=get_requests&status=' + filter)
                .then(function(response) {
                    if (response.data.success) {
                        $scope.filteredRequests = response.data.data;
                        $timeout(function() {
                            $scope.$apply();
                        });
                    } else {
                        console.error('Failed to load ' + filter + ' requests:', response.data.message);
                        alert('Failed to load ' + filter + ' requests: ' + response.data.message);
                        $scope.filteredRequests = [];
                    }
                })
                .catch(function(error) {
                    console.error('Error loading ' + filter + ' requests:', error);
                    alert('An error occurred while loading ' + filter + ' requests.');
                    $scope.filteredRequests = [];
                });
        }
    };

    // Get current year short format for Change No.
    $scope.getCurrentYearShort = function() {
        return new Date().getFullYear().toString().slice(-2);
    };

    // Submit the change request
    $scope.submitRequest = function() {
        if (!$scope.newRequestForm.$valid) {
            alert('Please fill in all required fields');
            return;
        }

        if (!$scope.newRequest.projectId && !$scope.newRequest.ticketId) {
            alert('Please select a valid Project ID or Ticket ID from the list.');
            return;
        }

        if ($scope.newRequest.projectId && $scope.newRequest.ticketId) {
            alert('Cannot select both a Project ID and a Ticket ID. Please choose one.');
            return;
        }

        if ($scope.newRequest.projectId && !$scope.projects.some(function(project) {
                return project.id === $scope.newRequest.projectId;
            })) {
            alert('Invalid Project ID. Please select a valid project ID from the list.');
            return;
        }

        if ($scope.newRequest.ticketId && !$scope.tickets.some(function(ticket) {
                return ticket.id === $scope.newRequest.ticketId;
            })) {
            alert('Invalid Ticket ID. Please select a valid ticket ID from the list.');
            return;
        }

        if (!$scope.user_info.user_id || isNaN($scope.user_info.user_id)) {
            console.error('submitRequest: Invalid user_info.user_id:', $scope.user_info.user_id);
            alert('User ID is missing or invalid. Please log in again.');
            check_auth.logout();
            window.location.href = '/login';
            return;
        }

        if (!$scope.newRequest.requestBy || isNaN($scope.newRequest.requestBy)) {
            console.error('submitRequest: Invalid newRequest.requestBy:', $scope.newRequest.requestBy);
            alert('Please select a valid user for Request By.');
            return;
        }

        if (!$scope.users.some(function(user) { return user.id === $scope.newRequest.requestBy; })) {
            console.error('submitRequest: requestBy not found in users:', $scope.newRequest.requestBy);
            alert('Invalid Request By user. Please select a valid user from the list.');
            return;
        }

        if (!$scope.newRequest.implementer || isNaN($scope.newRequest.implementer)) {
            console.error('submitRequest: Invalid newRequest.implementer:', $scope.newRequest.implementer);
            alert('Please select a valid user for Implementer.');
            return;
        }

        if (!$scope.users.some(function(user) { return user.id === $scope.newRequest.implementer; })) {
            console.error('submitRequest: implementer not found in users:', $scope.newRequest.implementer);
            alert('Invalid Implementer user. Please select a valid user from the list.');
            return;
        }

        if (!$scope.newRequest.clientName) {
            alert('Please select a valid client.');
            return;
        }

        $scope.isSubmitting = true;

        var requestData = angular.copy($scope.newRequest);

        if (requestData.dateRaised) {
            requestData.dateRaised = new Date(requestData.dateRaised).toISOString().split('T')[0];
        }
        if (requestData.implementationDate) {
            requestData.implementationDate = new Date(requestData.implementationDate).toISOString().split('T')[0];
        }

        if (requestData.startTime) {
            requestData.startTime = new Date(requestData.startTime).toISOString().substr(11, 8);
        }
        if (requestData.endTime) {
            requestData.endTime = new Date(requestData.endTime).toISOString().substr(11, 8);
        }

        requestData.request_by = parseInt($scope.newRequest.requestBy);
        requestData.implementer = parseInt($scope.newRequest.implementer);
        requestData.submitted_by = parseInt($scope.user_info.user_id);

        var mappedData = {
            project_id: $scope.newRequest.projectId || null,
            ticket_id: $scope.newRequest.ticketId || null,
            type: requestData.type,
            emergency_reason: requestData.emergencyReason || null,
            date_raised: requestData.dateRaised,
            client_affected: requestData.clientName,
            country: requestData.country,
            request_by: requestData.request_by,
            implementer: requestData.implementer,
            priority: requestData.priority,
            title: requestData.title,
            description: requestData.description,
            implementation_date: requestData.implementationDate,
            start_time: requestData.startTime || null,
            end_time: requestData.endTime || null,
            change_reason: requestData.changeReason,
            impact_assessment: requestData.impactAssessment,
            service_application: requestData.serviceApplication,
            affected_artifacts: requestData.affectedArtifacts,
            scope: requestData.scope,
            implementation_plan: requestData.implementationPlan,
            budget: requestData.budget || null,
            risk: requestData.risk || null,
            backout_plan: requestData.backoutPlan,
            resources_required: requestData.resourcesRequired || null,
            comments: requestData.comments || null,
            submitted_by: requestData.submitted_by
        };

        console.log('Submitting data:', mappedData);

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=create_request',
                data: mappedData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                $scope.isSubmitting = false;
                if (response.data.success) {
                    $scope.successProjectOrTicketId = $scope.newRequest.projectId ?
                        'PROJ-' + String(response.data.project_id).padStart(8, '0') :
                        'IN' + String(response.data.ticket_id).padStart(8, '0');
                    $scope.successProjectOrTicketLabel = $scope.newRequest.projectId ? 'Project ID' : 'Ticket ID';
                    $scope.successChangeNo = response.data.change_no;
                    $scope.showSuccessModal = true;
                    $scope.newRequest = {
                        submitted_by: $scope.user_info.user_id,
                        requestBy: $scope.user_info.user_id,
                        implementer: $scope.user_info.user_id,
                        projectId: null,
                        ticketId: null,
                        projectOrTicketIdFormatted: ''
                    };
                    $scope.newRequestForm.$setPristine();
                    $scope.newRequestForm.$setUntouched();
                    $scope.loadRequests();
                    if (response.data.message.includes('but some emails failed')) {
                        console.warn('Email sending issue:', response.data.message);
                        alert('Request submitted successfully, but some approval emails failed to send. Please contact support.');
                    }
                } else {
                    alert('Failed to submit request: ' + response.data.message);
                }
            })
            .catch(function(error) {
                $scope.isSubmitting = false;
                console.error('Error submitting request:', error);
                alert('An error occurred while submitting the request: ' + (error.data ? error.data.message : 'Unknown error'));
            });
    };

    // Approve request
    $scope.approveRequest = function() {
        if (!$scope.selectedRequest.id) {
            alert('No request selected.');
            return;
        }

        var approvalLevel = '';
        var comments = '';
        var approverId = $scope.user_info.user_id;

        // Determine the approval level the user is eligible for
        if ($scope.isUserEligible('ceo') && !$scope.selectedRequest.approval_status.ceo.comments) {
            approvalLevel = 'ceo';
            comments = $scope.approval.ceo.comments || '';
        } else if ($scope.isUserEligible('qa') && !$scope.selectedRequest.approval_status.qa.comments) {
            approvalLevel = 'qa';
            comments = $scope.approval.qa.comments || '';
        } else if ($scope.isUserEligible('dept_head') && !$scope.selectedRequest.approval_status.dept_head.comments) {
            approvalLevel = 'dept_head';
            comments = $scope.approval.deptHead.comments || '';
        } else {
            alert('You are not authorized to approve at any level or all eligible levels have already been approved/rejected.');
            return;
        }

        if (!approverId) {
            console.error('No valid approver ID found for', approvalLevel);
            alert('Invalid approver ID.');
            return;
        }

        var approvalData = {
            request_id: $scope.selectedRequest.id,
            approver_id: approverId,
            approval_level: approvalLevel,
            status: 'approved',
            comments: comments || null
        };

        console.log('Sending approval data:', approvalData);

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=update_approval',
                data: approvalData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                if (response.data.success) {
                    alert('Approval saved successfully.');
                    // Update the approval object for the specific level
                    var levelKey = approvalLevel === 'dept_head' ? 'deptHead' : approvalLevel;
                    $scope.approval[levelKey] = {
                        name: $scope.user_info.first_name + ' ' + $scope.user_info.last_name,
                        date: new Date(),
                        dateFormatted: $scope.formatDateForInput(new Date()),
                        comments: comments || '',
                        approved: true
                    };
                    // Update selectedRequest.approval_status
                    $scope.selectedRequest.approval_status[approvalLevel] = {
                        approved: true,
                        name: $scope.user_info.first_name + ' ' + $scope.user_info.last_name,
                        date: new Date().toISOString(),
                        comments: comments || ''
                    };
                    $scope.checkAllApprovalsComplete();
                    $scope.loadRequests();
                    $timeout(function() {
                        $scope.$apply();
                    });
                } else {
                    alert('Failed to save approval: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error saving approval:', error);
                alert('An error occurred while saving the approval.');
            });
    };

    // Reject request
    $scope.rejectRequest = function() {
        if (!$scope.selectedRequest.id) {
            alert('No request selected.');
            return;
        }

        var approvalLevel = '';
        var comments = '';
        var approverId = $scope.user_info.user_id;

        // Determine the approval level the user is eligible for
        if ($scope.isUserEligible('ceo') && !$scope.selectedRequest.approval_status.ceo.comments) {
            approvalLevel = 'ceo';
            comments = $scope.approval.ceo.comments || '';
        } else if ($scope.isUserEligible('qa') && !$scope.selectedRequest.approval_status.qa.comments) {
            approvalLevel = 'qa';
            comments = $scope.approval.qa.comments || '';
        } else if ($scope.isUserEligible('dept_head') && !$scope.selectedRequest.approval_status.dept_head.comments) {
            approvalLevel = 'dept_head';
            comments = $scope.approval.deptHead.comments || '';
        } else {
            alert('You are not authorized to reject at any level or all eligible levels have already been approved/rejected.');
            return;
        }

        if (!approverId) {
            console.error('No valid approver ID found for', approvalLevel);
            alert('Invalid approver ID.');
            return;
        }

        var approvalData = {
            request_id: $scope.selectedRequest.id,
            approver_id: approverId,
            approval_level: approvalLevel,
            status: 'rejected',
            comments: comments || null
        };

        console.log('Sending rejection data:', approvalData);

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=update_approval',
                data: approvalData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                if (response.data.success) {
                    alert('Rejection saved successfully.');
                    // Update the approval object for the specific level
                    var levelKey = approvalLevel === 'dept_head' ? 'deptHead' : approvalLevel;
                    $scope.approval[levelKey] = {
                        name: $scope.user_info.first_name + ' ' + $scope.user_info.last_name,
                        date: new Date(),
                        dateFormatted: $scope.formatDateForInput(new Date()),
                        comments: comments || '',
                        approved: false
                    };
                    // Update selectedRequest.approval_status
                    $scope.selectedRequest.approval_status[approvalLevel] = {
                        approved: false,
                        name: $scope.user_info.first_name + ' ' + $scope.user_info.last_name,
                        date: new Date().toISOString(),
                        comments: comments || ''
                    };
                    $scope.checkAllApprovalsComplete();
                    $scope.loadRequests();
                    $timeout(function() {
                        $scope.$apply();
                    });
                } else {
                    alert('Failed to save rejection: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error saving rejection:', error);
                alert('An error occurred while saving the rejection.');
            });
    };

    // Save changes for implementation status and duration
    $scope.saveChanges = function() {
        if (!$scope.selectedRequest.id) {
            alert('No request selected.');
            return;
        }

        if (!$scope.allApprovalsComplete) {
            alert('Cannot save implementation details until all approval stages are complete.');
            return;
        }

        if (!$scope.selectedRequest.implementation_status) {
            alert('Please select an implementation status.');
            return;
        }

        if ($scope.selectedRequest.implementation_start_hours &&
            ($scope.selectedRequest.implementation_start_hours < 1 || $scope.selectedRequest.implementation_start_hours > 12)) {
            alert('Start hours must be between 1 and 12');
            return;
        }
        if ($scope.selectedRequest.implementation_start_minutes &&
            ($scope.selectedRequest.implementation_start_minutes < 0 || $scope.selectedRequest.implementation_start_minutes > 59)) {
            alert('Start minutes must be between 0 and 59');
            return;
        }
        if ($scope.selectedRequest.implementation_end_hours &&
            ($scope.selectedRequest.implementation_end_hours < 1 || $scope.selectedRequest.implementation_end_hours > 12)) {
            alert('End hours must be between 1 and 12');
            return;
        }
        if ($scope.selectedRequest.implementation_end_minutes &&
            ($scope.selectedRequest.implementation_end_minutes < 0 || $scope.selectedRequest.implementation_end_minutes > 59)) {
            alert('End minutes must be between 0 and 59');
            return;
        }

        var updateData = {
            request_id: $scope.selectedRequest.id,
            implementation_status: $scope.selectedRequest.implementation_status,
            implementation_start: $scope.formatTimeForDatabase(
                $scope.selectedRequest.implementation_start_hours,
                $scope.selectedRequest.implementation_start_minutes,
                $scope.selectedRequest.implementation_start_period
            ) || $scope.selectedRequest.implementation_start,
            implementation_end: $scope.formatTimeForDatabase(
                $scope.selectedRequest.implementation_end_hours,
                $scope.selectedRequest.implementation_end_minutes,
                $scope.selectedRequest.implementation_end_period
            ) || $scope.selectedRequest.implementation_end,
            implementation_notes: $scope.selectedRequest.implementation_notes || null
        };

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=update_implementation',
                data: updateData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                if (response.data.success) {
                    alert('Implementation status updated successfully.');
                    $scope.selectedRequest.implementation_status = response.data.data.implementation_status;
                    $scope.selectedRequest.implementation_start = response.data.data.implementation_start || null;
                    $scope.selectedRequest.implementation_end = response.data.data.implementation_end || null;
                    $scope.selectedRequest.implementation_notes = response.data.data.implementation_notes || null;
                    const startTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_start);
                    const endTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_end);
                    $scope.selectedRequest.implementation_start_hours = startTime.hours;
                    $scope.selectedRequest.implementation_start_minutes = startTime.minutes;
                    $scope.selectedRequest.implementation_start_period = startTime.period;
                    $scope.selectedRequest.implementation_end_hours = endTime.hours;
                    $scope.selectedRequest.implementation_end_minutes = endTime.minutes;
                    $scope.selectedRequest.implementation_end_period = endTime.period;
                    $scope.isImplementationSaved = ['Successful', 'Failed', 'Rescheduled'].includes($scope.selectedRequest.implementation_status);
                    $scope.loadRequests();
                } else {
                    alert('Failed to update implementation status: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error updating implementation status:', error);
                alert('An error occurred while updating the implementation status.');
            });
    };

    // Download PDF
    $scope.downloadPDF = function() {
        if (!$scope.selectedRequest.id) {
            alert('No request selected to download.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });

        const pageWidth = 210;
        const pageHeight = 297;
        const margin = 10;
        const maxWidth = pageWidth - 2 * margin;
        const columnWidth = (maxWidth - 5) / 2;
        let y = margin;

        function checkPageBreak(additionalHeight) {
            if (y + additionalHeight > pageHeight - margin) {
                doc.addPage();
                y = margin + 20;
                return true;
            }
            return false;
        }

        function addHeader() {
            doc.setFontSize(20);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(40, 40, 40);
            doc.text('Change Request Report', pageWidth / 2, 15, { align: "center" });

            doc.setFontSize(10);
            doc.setFont("helvetica", "italic");
            doc.setTextColor(100);
            doc.text('Generated on: ' + new Date().toLocaleString(), pageWidth - margin, 15, { align: "right" });

            y = 30;
        }

        function addFooter() {
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(9);
                doc.setTextColor(150);
                doc.text('Confidential - Internal Use Only', margin, pageHeight - 8);
                doc.text(`Page ${i} of ${pageCount}`, pageWidth - margin, pageHeight - 8, { align: "right" });
            }
        }

        function addSectionTitle(title) {
            checkPageBreak(12);
            doc.setFillColor(94, 114, 228);
            doc.rect(margin, y, pageWidth - 2 * margin, 8, 'F');
            doc.setFontSize(12);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(255, 255, 255);
            doc.text(title, margin + 2, y + 6);
            y += 14;
        }

        function addField(label, value, column = 0) {
            if (!value || value === 'N/A') return 0;
            const x = margin + (column * (columnWidth + 5));
            doc.setFontSize(9);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(50, 50, 50);
            doc.text(label + ':', x, y);

            doc.setFont("helvetica", "normal");
            const splitText = doc.splitTextToSize(value, columnWidth - 6);
            const textHeight = splitText.length * 5;
            checkPageBreak(textHeight + 12);

            doc.setFillColor(245, 246, 250);
            doc.roundedRect(x, y + 2, columnWidth, textHeight + 8, 2, 2, 'F');
            doc.text(splitText, x + 3, y + 8);

            return textHeight + 12;
        }

        addHeader();

        addSectionTitle('Basic Information');
        const basicFields = [
            { label: 'Type of Change', value: $scope.selectedRequest.type },
            { label: 'Reason for Emergency', value: $scope.selectedRequest.emergency_reason },
            { label: 'Date Raised', value: $scope.selectedRequest.date_raised_formatted },
            { label: 'Global / Customer Name', value: $scope.selectedRequest.client_name },
            { label: 'Country', value: $scope.selectedRequest.country },
            {
                label: 'Request No',
                value: $scope.selectedRequest.project_id ?
                    'PROJ-' + String($scope.selectedRequest.project_id).padStart(8, '0') : $scope.selectedRequest.ticket_id ?
                    'IN' + String($scope.selectedRequest.ticket_id).padStart(8, '0') : 'N/A'
            },
            { label: 'Request By', value: $scope.selectedRequest.request_by_name },
            { label: 'Priority', value: $scope.selectedRequest.priority }
        ];
        let maxHeight = 0;
        for (let i = 0; i < basicFields.length; i++) {
            const column = i % 2;
            if (column === 0 && i > 0) {
                y += maxHeight;
                maxHeight = 0;
            }
            const height = addField(basicFields[i].label, basicFields[i].value, column);
            maxHeight = Math.max(maxHeight, height);
        }
        y += maxHeight;

        addSectionTitle('Change Details');
        const changeFields = [
            { label: 'Change Name', value: $scope.selectedRequest.title },
            { label: 'Implementation Date Required', value: $scope.selectedRequest.implementation_date_formatted },
            { label: 'Implementation Start Time', value: $scope.selectedRequest.start_time },
            { label: 'Implementation End Time', value: $scope.selectedRequest.end_time },
            { label: 'Implementer', value: $scope.selectedRequest.implementer_name },
            { label: 'Reason for Change', value: $scope.selectedRequest.change_reason },
            { label: 'Description of Change', value: $scope.selectedRequest.description, fullWidth: true }
        ];
        maxHeight = 0;
        for (let i = 0; i < changeFields.length; i++) {
            if (changeFields[i].fullWidth) {
                y += maxHeight;
                maxHeight = 0;
                const height = addField(changeFields[i].label, changeFields[i].value, 0);
                y += height;
            } else {
                const column = i % 2;
                if (column === 0 && i > 0) {
                    y += maxHeight;
                    maxHeight = 0;
                }
                const height = addField(changeFields[i].label, changeFields[i].value, column);
                maxHeight = Math.max(maxHeight, height);
            }
        }
        y += maxHeight;

        addSectionTitle('Impact Assessment');
        const impactFields = [
            { label: 'Service/Application', value: $scope.selectedRequest.service_application },
            { label: 'Affected Artifacts', value: $scope.selectedRequest.affected_artifacts },
            { label: 'Scope', value: $scope.selectedRequest.scope },
            { label: 'Budget', value: $scope.selectedRequest.budget },
            { label: 'Risk', value: $scope.selectedRequest.risk },
            { label: 'Resources Required', value: $scope.selectedRequest.resources_required },
            { label: 'Other Comments', value: $scope.selectedRequest.comments },
            { label: 'Change Impact Assessment', value: $scope.selectedRequest.impact_assessment, fullWidth: true },
            { label: 'Implementation Plan', value: $scope.selectedRequest.implementation_plan, fullWidth: true },
            { label: 'Back out Plan', value: $scope.selectedRequest.backout_plan, fullWidth: true }
        ];
        maxHeight = 0;
        for (let i = 0; i < impactFields.length; i++) {
            if (impactFields[i].fullWidth) {
                y += maxHeight;
                maxHeight = 0;
                const height = addField(impactFields[i].label, impactFields[i].value, 0);
                y += height;
            } else {
                const column = i % 2;
                if (column === 0 && i > 0) {
                    y += maxHeight;
                    maxHeight = 0;
                }
                const height = addField(impactFields[i].label, impactFields[i].value, column);
                maxHeight = Math.max(maxHeight, height);
            }
        }
        y += maxHeight;

        addSectionTitle('Approval Details');
        const headers = ['Approved By', 'Name', 'Date', 'Comments', 'Status'];
        const colWidths = [40, 40, 30, 50, 30];
        const rowHeight = 10;
        const cellPadding = 2;

        doc.setFontSize(10);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(255, 255, 255);
        doc.setFillColor(94, 114, 228);
        checkPageBreak(rowHeight);
        headers.forEach((header, i) => {
            doc.roundedRect(margin + colWidths.slice(0, i).reduce((a, b) => a + b, 0), y, colWidths[i], rowHeight, 2, 2, 'F');
            doc.text(header, margin + colWidths.slice(0, i).reduce((a, b) => a + b, 0) + cellPadding, y + rowHeight - cellPadding);
        });
        y += rowHeight;

        doc.setFont("helvetica", "normal");
        doc.setTextColor(0, 0, 0);
        const approvals = [{
                role: 'Department Head',
                name: $scope.approval.deptHead.comments ? ($scope.approval.deptHead.name || 'N/A') : 'N/A',
                date: $scope.approval.deptHead.comments ? ($scope.approval.deptHead.dateFormatted || 'N/A') : 'N/A',
                comments: $scope.approval.deptHead.comments || 'N/A',
                status: $scope.approval.deptHead.comments ? ($scope.approval.deptHead.approved ? 'Approved' : 'Rejected') : 'Pending'
            },
            {
                role: 'Quality Assurance',
                name: $scope.approval.qa.comments ? ($scope.approval.qa.name || 'N/A') : 'N/A',
                date: $scope.approval.qa.comments ? ($scope.approval.qa.dateFormatted || 'N/A') : 'N/A',
                comments: $scope.approval.qa.comments || 'N/A',
                status: $scope.approval.qa.comments ? ($scope.approval.qa.approved ? 'Approved' : 'Rejected') : 'Pending'
            },
            {
                role: 'CEO/Senior Manager',
                name: $scope.approval.ceo.comments ? ($scope.approval.ceo.name || 'N/A') : 'N/A',
                date: $scope.approval.ceo.comments ? ($scope.approval.ceo.dateFormatted || 'N/A') : 'N/A',
                comments: $scope.approval.ceo.comments || 'N/A',
                status: $scope.approval.ceo.comments ? ($scope.approval.ceo.approved ? 'Approved' : 'Rejected') : 'Pending'
            }
        ];

        approvals.forEach((row, index) => {
            checkPageBreak(rowHeight);
            const rowData = [row.role, row.name, row.date, row.comments, row.status];
            const isEven = index % 2 === 0;
            rowData.forEach((cell, i) => {
                const splitText = doc.splitTextToSize(cell, colWidths[i] - 2 * cellPadding);
                if (i === 4) {
                    if (row.status === 'Approved') {
                        doc.setFillColor(212, 237, 218);
                    } else if (row.status === 'Rejected') {
                        doc.setFillColor(248, 215, 218);
                    } else if (row.status === 'Pending') {
                        doc.setFillColor(255, 243, 205);
                    }
                } else {
                    doc.setFillColor(isEven ? 255 : 245, isEven ? 245 : 245, isEven ? 255 : 245);
                }
                doc.roundedRect(margin + colWidths.slice(0, i).reduce((a, b) => a + b, 0), y, colWidths[i], rowHeight, 2, 2, 'F');
                doc.text(splitText, margin + colWidths.slice(0, i).reduce((a, b) => a + b, 0) + cellPadding, y + rowHeight - cellPadding);
            });
            y += rowHeight;
        });

        y += 10;

        addSectionTitle('Implementation Status and Duration');
        const implFields = [
            { label: 'Implementation Status', value: $scope.selectedRequest.implementation_status },
            { label: 'Duration Start', value: $scope.selectedRequest.implementation_start },
            { label: 'Duration End', value: $scope.selectedRequest.implementation_end },
            { label: 'Implementation Notes', value: $scope.selectedRequest.implementation_notes, fullWidth: true }
        ];
        maxHeight = 0;
        for (let i = 0; i < implFields.length; i++) {
            if (implFields[i].fullWidth) {
                y += maxHeight;
                maxHeight = 0;
                const height = addField(implFields[i].label, implFields[i].value, 0);
                y += height;
            } else {
                const column = i % 2;
                if (column === 0 && i > 0) {
                    y += maxHeight;
                    maxHeight = 0;
                }
                const height = addField(implFields[i].label, implFields[i].value, column);
                maxHeight = Math.max(maxHeight, height);
            }
        }
        y += maxHeight;

        addFooter();
        const changeNo = $scope.selectedRequest.change_no ? String($scope.selectedRequest.change_no).replace(/[^a-zA-Z0-9]/g, '_') : 'Details';
        doc.save(`Change_Request_${changeNo}.pdf`);
    };

    // Go back to change request page
    $scope.goBack = function() {
        window.location.href = 'change_request';
    };

    // Close success modal and refresh page
    $scope.closeSuccessModal = function() {
        $scope.showSuccessModal = false;
        window.location.href = 'change_request';
    };

    // Initialize the controller
    $scope.init = function() {
        $scope.loadUserInfo();
        $scope.loadReferenceData();
        $scope.loadClients();
        $scope.loadUsers();
        $scope.loadProjects();
        $scope.loadTickets();
        $scope.loadRequests();
        $scope.loadRequestDetails();
    };

    $scope.init();
}]);