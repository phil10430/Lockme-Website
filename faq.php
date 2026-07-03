<?php
session_start();
$bodyClass = ""; // keine landing page
require_once __DIR__ . '/templates/header.php';
?>

<div class="faq-wrapper">

    <div class="faq-container">

        <div class="faq-item">
            <button class="faq-question">How does locking work?</button>
            <div class="faq-answer">
                Connect your Phone to the LockMeBox via Bluetooth within the LockMeBox-App. You can lock it via password or via timer.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">Is there a way to open the box when you loose your password?</button>
            <div class="faq-answer">
                No, there is no recovery.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">What happens when your LockMeBox is locked and the battery is empty?</button>
            <div class="faq-answer">
                Your LockMeBox will stay locked until you recharge it.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">Why should I get LockMeBox Pro?</button>
            <div class="faq-answer">
                LockMeBox Pro offers you remote control via web dashbord. It comes also with advanced control features e.g. extend timer or lock with timer and password simultaneously.
            </div>
        </div>

         <div class="faq-item">
            <button class="faq-question">Can I control LockMeBox remotely?</button>
            <div class="faq-answer">
                Yes, if you have an active pro subscription you can only control LockMeBox from anywhere via our web dashboard.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">LockMeBox Pro: How does remote control work?</button>
            <div class="faq-answer">
                    Create an account on lockmebox.com. 
                    Sign in on your smartphone and connect to your LockMeBox.
                    The person who controls remotely has to sign in with the same account on lockmebox.com.
            </div>
        </div>

          <div class="faq-item">
            <button class="faq-question">LockMeBox Pro: The web dashboard still shows Free although I have subscribed to Pro ?</button>
            <div class="faq-answer">
                    Sign out in your LockMeBox app. Ensure Pro is active in your App. Sign in in your app again. 
                    Ensure you are signed in your app and the web dashboard with the same account.
                    Now the web dashboard should be updated to Pro.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">LockMeBox Pro: What is the renewal cost for the Pro 1-year license after the first year?</button>
            <div class="faq-answer">
                To cover ongoing server maintenance and remote web control features, we reserve the right to apply up to a 10% price increase upon renewal. 
                This ensures your dedicated server features remain fast, secure, and 100% reliable.
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">LockMeBox Pro: Does the box still work with the basic (non-Pro) app for Bluetooth timer and password locking if I choose not to renew the Pro license?</button>
            <div class="faq-answer">
               Yes.
            </div>
        </div>
         <div class="faq-item">
            <button class="faq-question">LockMeBox Pro: Are the Remote Web Control, Extend Timer, and Random Timer features exclusive to the Pro version?</button>
            <div class="faq-answer">
                Yes.
            </div>
        </div>
    </div>

</div>

<script>
document.querySelectorAll(".faq-question").forEach(btn => {
    btn.addEventListener("click", () => {
        const item = btn.parentElement;
        item.classList.toggle("active");
    });
});
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>