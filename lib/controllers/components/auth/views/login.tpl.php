<div id="login-box">
    <h2>Login</h2>  
    
    <?php if ($login_message != ''): ?>
    <div id="login-error">
        <?= $login_message ?>
    </div>
    <?php endif; ?>
    
    <?= 
        $this->helpers->form->open() .
        $this->helpers->form->get_text_field('Username', 'username') .
        $this->helpers->form->get_password_field('Password', 'password') .
        $this->helpers->form->close()
    ?>
</div>
