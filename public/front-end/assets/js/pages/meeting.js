var meeting_id = $('#meeting_id').val();
var base_url = $('#base_url').val();
var is_meeting_admin = $('#is_meeting_admin').val();
const domain = 'meet.guifi.net';
const options = {
    roomName: $('#room_name').val(),
    parentNode: document.querySelector('#meet'),
    userInfo: {
        email: $('#user_email').val(),
        displayName: $('#user_name').val()
    },
    SHOW_PROMOTIONAL_CLOSE_PAGE: false
};
const api = new JitsiMeetExternalAPI(domain, options);
