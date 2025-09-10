angular.module('sheetApp').filter('pad', function() {
    return function(input, length) {
        if (!input) return '';
        return String(input).padStart(length, '0');
    };
}).controller('ClientLogsCtrl', ['$scope', '$http', '$localStorage', 'check_auth', 'myConfig',
    function($scope, $http, $localStorage, check_auth, myConfig) {
        // Initialize variables
        $scope.user_info = {};
        $scope.profile_pic_true = false;
        $scope.profile_pic = '';
        $scope.clients = [];
        $scope.clientsWithEmails = [];
        $scope.allClients = { id: '', name: 'All', Email: '' };
        $scope.selectedClient = $scope.allClients;
        $scope.requests = [];
        $scope.currentPage = 1;
        $scope.itemsPerPage = 10;
        $scope.totalPages = 1;
        $scope.loading = false;
        $scope.error = '';
        $scope.searchQuery = '';
        $scope.selectedEmails = [];
        $scope.customEmails = '';
        $scope.emailModal = null;

        // Load user info
        $scope.loadUserInfo = function() {
            try {
                if (!$localStorage.user_info || !$localStorage.user_info.data || !$localStorage.user_info.data.user_id) {
                    console.error('Invalid or missing user info in localStorage');
                    check_auth.logout();
                    window.location.href = '/login';
                    return;
                }

                check_auth.verify_auth($localStorage.user_info);
                $scope.user_info = $localStorage.user_info.data || {};
                console.log('User info from localStorage:', $scope.user_info);

                if ($scope.user_info.profile_pic) {
                    $scope.profile_pic_true = true;
                    $scope.profile_pic = myConfig.file_url + $scope.user_info.profile_pic;
                }
            } catch (e) {
                console.error('Error in loadUserInfo:', e);
                check_auth.logout();
                window.location.href = '/login';
            }
        };

        // Load clients from external API
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
                                country: client.Country,
                                Email: client.Email
                            };
                        });

                    // Store clients with emails for the modal
                    $scope.clientsWithEmails = response.data
                        .filter(function(client) {
                            return (client.is_active === "1" || client.is_active === 1) && client.Email;
                        })
                        .map(function(client) {
                            return {
                                Company_Id: client.Company_Id,
                                Company_Name: client.Company_Name,
                                Email: client.Email,
                                selected: false
                            };
                        });

                    $scope.clients.unshift($scope.allClients);
                    console.log('Clients loaded from external API:', $scope.clients);
                    console.log('Clients with emails:', $scope.clientsWithEmails);
                })
                .catch(function(error) {
                    console.error('Error loading clients from external API:', error);
                    $scope.error = 'Failed to load clients from external API.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load clients from external API.'
                    });
                });
        };

        // Load change requests for selected client
        $scope.loadClientRequests = function() {
            $scope.loading = true;
            $scope.error = '';

            let url = 'apiSheet/client_logs.php?action=get_client_requests&page=' + $scope.currentPage + '&limit=' + $scope.itemsPerPage;
            if ($scope.selectedClient && $scope.selectedClient.id) {
                url += '&client_id=' + $scope.selectedClient.id;
                console.log('Loading requests for client ID:', $scope.selectedClient.id, 'Name:', $scope.selectedClient.name);
            } else {
                console.log('Loading all requests');
            }

            $http.get(url)
                .then(function(response) {
                    $scope.loading = false;
                    if (response.data.success) {
                        $scope.requests = response.data.data.requests;
                        $scope.totalPages = response.data.data.total_pages;
                        console.log('Change requests loaded:', $scope.requests.length, 'requests');
                    } else {
                        console.error('Failed to load change requests:', response.data.message);
                        $scope.error = 'Failed to load change requests: ' + response.data.message;
                        $scope.requests = [];
                        $scope.totalPages = 1;
                    }
                })
                .catch(function(error) {
                    $scope.loading = false;
                    console.error('Error loading change requests:', error);
                    if (error.data && error.data.message) {
                        $scope.error = 'An error occurred: ' + error.data.message;
                    } else {
                        $scope.error = 'An error occurred while loading change requests.';
                    }
                    $scope.requests = [];
                    $scope.totalPages = 1;
                });
        };

        // Change page
        $scope.changePage = function(page) {
            if (page >= 1 && page <= $scope.totalPages) {
                $scope.currentPage = page;
                $scope.loadClientRequests();
            }
        };

        // Logout function
        $scope.logout = function() {
            check_auth.logout($scope.user_info.user_id);
        };

        // Toggle email selection
        $scope.toggleEmailSelection = function(client) {
            if (client.selected) {
                // Add email if not already in the list
                if ($scope.selectedEmails.indexOf(client.Email) === -1) {
                    $scope.selectedEmails.push(client.Email);
                }
            } else {
                // Remove email from the list
                const index = $scope.selectedEmails.indexOf(client.Email);
                if (index !== -1) {
                    $scope.selectedEmails.splice(index, 1);
                }
            }
        };

        // Remove email from selection
        $scope.removeEmail = function(email) {
            const index = $scope.selectedEmails.indexOf(email);
            if (index !== -1) {
                $scope.selectedEmails.splice(index, 1);

                // Also uncheck the corresponding client checkbox
                const client = $scope.clientsWithEmails.find(c => c.Email === email);
                if (client) {
                    client.selected = false;
                }
            }
        };

        // Open email modal
        $scope.openEmailModal = function() {
            if ($scope.requests.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'No change requests to send. Please load requests first.'
                });
                return;
            }

            // Reset selections
            $scope.selectedEmails = [];
            $scope.customEmails = '';

            // Uncheck all client checkboxes
            $scope.clientsWithEmails.forEach(client => {
                client.selected = false;
            });

            // Auto-select current client email if available
            if ($scope.selectedClient && $scope.selectedClient.id && $scope.selectedClient.Email) {
                const client = $scope.clientsWithEmails.find(c => c.Email === $scope.selectedClient.Email);
                if (client) {
                    client.selected = true;
                    $scope.selectedEmails.push(client.Email);
                }
            }

            // Initialize modal if not already done
            if (!$scope.emailModal) {
                const modalElement = document.getElementById('emailModal');
                $scope.emailModal = new bootstrap.Modal(modalElement);
            }

            // Show the modal
            $scope.emailModal.show();
        };

        // Close email modal
        $scope.closeEmailModal = function() {
            if ($scope.emailModal) {
                $scope.emailModal.hide();
            }
        };

        // Send email with PDF (server-side generation)
        $scope.sendEmail = function() {
            // Combine selected and custom emails
            let emails = [...$scope.selectedEmails];

            if ($scope.customEmails) {
                const custom = $scope.customEmails.split(',').map(email => email.trim()).filter(email => email);
                emails = [...new Set([...emails, ...custom])]; // Remove duplicates
            }

            if (emails.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select at least one email address or add custom emails.'
                });
                return;
            }

            // Validate emails
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const invalidEmails = emails.filter(email => !emailRegex.test(email));
            if (invalidEmails.length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'The following emails are invalid: ' + invalidEmails.join(', ')
                });
                return;
            }

            // Show loading indicator
            Swal.fire({
                title: 'Sending email...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send request to server to generate PDF and send email
            $http({
                method: 'POST',
                url: 'apiSheet/sendClientEmail.php',
                headers: { 'Content-Type': 'application/json' },
                data: {
                    emails: emails,
                    client_id: $scope.selectedClient.id || null,
                    client_name: $scope.selectedClient.name || 'All Clients'
                }
            }).then(function(response) {
                Swal.close();
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Email sent successfully to ' + emails.join(', ')
                    });
                    $scope.closeEmailModal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.data.message || 'Failed to send email.'
                    });
                }
            }).catch(function(error) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while sending the email: ' + ((error.data && error.data.message) ? error.data.message : 'Unknown error')
                });
            });
        };

        // Initialize the controller
        $scope.init = function() {
            $scope.loadUserInfo();
            $scope.loadClients();
            setTimeout(function() {
                $scope.loadClientRequests();
            }, 500);
        };

        $scope.init();
    }
]);