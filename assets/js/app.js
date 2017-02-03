function set_token(token) {
  $("#ctrl-token").html(token);
}

function get_token() {
  return $("#ctrl-token").html();
}

function clear_token() {
  $("#ctrl-token").html('');
}

function attempt_login(event) {
  event.preventDefault();

  $("#ctrl-login-error").hide();
  $.post("/member_api/login", $(this).serialize())
    .done(function(data) {
      if (data.status !== 'SUCCESS') {
        $("#ctrl-login-error").show();
        return false;
      }

      set_token(data.token);

      $('#login-modal').modal('hide');
      $(".ctrl-user").show();
      $(".ctrl-login").hide();
    });
}

function is_login() {
  if (!get_token()) {
    return false;
  }

  return true;
}

function attempt_logout() {
  $.get("/member_api/logout")
    .done(function(data) {
      if (data.status !== 'SUCCESS') {
        $("#ctrl-login-error").show();
        return false;
      }
      clear_token();
      $(".ctrl-login").show();
      $('#login-modal').modal('show');
      $(".ctrl-user").hide();
    })
    .fail(function() {
      console.log('Fail!');
    });
}

$(document).ready(function() {

  $("#ctrl-login-error").hide();
  $("#ctrl-login-connection-error").hide();
  $(".ctrl-user").hide();
  $('#login-modal').modal('hide');

  $("form[action='#login']").on("submit", attempt_login);
  $("a[href='#login']").on("click", function() {
    $('#login-modal').modal('show');
  });

  $("a[href='#logout']").on("click", attempt_logout);

});
