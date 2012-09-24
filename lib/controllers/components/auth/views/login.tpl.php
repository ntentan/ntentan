<div id="login-box">
    <h2>Login</h2>  
    
    <?php if ($login_message != ''): ?>
    <div id="login-error">
        <?= $login_message ?>
    </div>
    <?php endif; ?>
    
    <?= 
        $helpers->form->open() .
        $helpers->form->get_text_field('Username', 'username') .
        $helpers->form->get_password_field('Password', 'password') .
        $helpers->form->close()
    ?>
</div>
