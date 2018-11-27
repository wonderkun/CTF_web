<?php if(!class_exists("View", false)) exit("no direct access allowed");?><div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
	  <div class="container-fluid">
		<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		</button>
		<a class="brand" href="#">HardPHP</a>
		<div class="nav-collapse collapse">
		  <p class="navbar-text pull-right">
			<span>Now: <?php echo htmlspecialchars($now, ENT_QUOTES, "UTF-8"); ?></span>
			<a href="<?php echo url(array('c'=>"main", 'a'=>"LoginOut", ));?>" class="navbar-link">Loginout</a>
		  </p>
		  <ul class="nav">
			<li class="active"><a href="<?php echo url(array('c'=>"main", 'a'=>"index", ));?>">Home</a></li>
			<li><a href="<?php echo url(array('c'=>"main", 'a'=>"Message", ));?>">Message</a></li>
			<li><a href="<?php echo url(array('c'=>"main", 'a'=>"Post", ));?>">Post</a></li>
		  </ul>
		</div><!--/.nav-collapse -->
	  </div>
	</div>
  </div>
  <div class="content"> 
		<div class="row hero-unit">
				<div class="span3">
					<h4>picture:</h4>
					<img src="<?php echo htmlspecialchars($picSrc, ENT_QUOTES, "UTF-8"); ?>" class="img-circle">	
				</div>
				<div class="span2">
				</div>
				<div class="span3">
					<h4>profile</h4>
					<p class="lead">username: <?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?></p>
					<p class="lead"> loginTime:<?php echo htmlspecialchars($loginTime, ENT_QUOTES, "UTF-8"); ?></p>
				</div>
		</div>
		<div class="row hero-unit">
			<h5>change picture:</h5>
			<form action="<?php echo url(array('c'=>"main", 'a'=>"Upload", ));?>"  method="post" enctype="multipart/form-data" >
				<input type="file" name="upfile" />
				<input type="submit" value="upload" />
			</form>
		</div>
	</div>