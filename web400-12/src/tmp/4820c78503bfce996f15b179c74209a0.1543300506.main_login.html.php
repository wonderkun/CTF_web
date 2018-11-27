<?php if(!class_exists("View", false)) exit("no direct access allowed");?>
<div class="form-content">
    <form class="form-signin" action="#" method="post">
            <h2 class="form-signin-heading">Please login</h2>
            <input  name="username" type="text" class="input-block-level"  placeholder="username">
            <input name="password" type="password" class="input-block-level" placeholder="Password">
            <label class="checkbox">
                <input type="checkbox" value="remember-me"> Remember me
            </label>
            <button class="btn btn-large btn-primary" type="submit">login</button>
    </form>
</div>