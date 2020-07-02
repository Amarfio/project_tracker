// var SessionTimeout = function () {
//     var i = function () {
//         $.sessionTimeout({
//             title: "Session Timeout Notification",
//             message: "Your session is expiring soon.",
//             redirUrl: window.location.pathname + "/logout",
//             logoutUrl: window.location.pathname + "/logout",
//             warnAfter: 12e4,
//             redirAfter: 18e4,
//             ignoreUserActivity: 0,
//             countdownMessage: "Redirecting in {timer} seconds.",
//             countdownBar: !0
//         })
//     };
//     return {
//         init: function () {
//             i()
//         }
//     }
// }();
// jQuery(document).ready(function () {
//     SessionTimeout.init()
// });