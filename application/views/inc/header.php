<?php include('doc-open.php'); ?>
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#"><?php echo lang('site_name'); ?></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="#"><?php echo lang('Catalog'); ?></a></li>
        <li><a href="#"><?php echo lang('Members'); ?></a></li>
      </ul>
      <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="<?php echo lang('Search for...'); ?>">
        </div>
        <button type="submit" class="hide"></button>
      </form>
      <ul class="nav navbar-nav navbar-right">
        <li class="ctrl-login"><a href="#login"><?php echo lang('Login'); ?></a></li>
        <li class="ctrl-user"><a href="#">Iqbal </a></li>
        <li class="dropdown ctrl-user">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-th-list"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#"><?php echo lang('Settings'); ?></a></li>
            <li><a href="#logout"><?php echo lang('Logout'); ?></a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<div class="modal fade ctrl-login" id="login-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo lang('Login'); ?></h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning" id="ctrl-login-error">
          <?php echo lang('Email or Password is invalid'); ?>
        </div>
        <div class="alert alert-warning" id="ctrl-login-connection-error">
          <?php echo lang('Error connection'); ?>
        </div>
        <form action="#login">
          <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="<?php echo lang('Email'); ?>" required>
          </div>
          <div class="form-group">
            <input type="password" class="form-control" name="password" placeholder="<?php echo lang('Password'); ?>" required>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-default"><?php echo lang('Login'); ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
