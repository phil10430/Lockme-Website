<?php
session_start();
$bodyClass = ""; // keine landing page
require_once __DIR__ . '/templates/header.php';
?>

<div class="faq-wrapper">

    <div class="faq-container">

        <h1>FAQ</h1>
        <p class="faq-subtitle">Frequently asked questions about LockMeBox</p>

        <div class="faq-item">
            <button class="faq-question">How does locking work?</button>
            <div class="faq-answer">
                Connect your Android Phone to the LockMeBox via Bluetooth. You can lock a box with a password or for a specific time or condition.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">Can I control LockMeBox from another phone?</button>
            <div class="faq-answer">
                No, you can only control LockMeBox with the phone which is connected via Bluetooth.
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">Will there be remote control possible?</button>
            <div class="faq-answer">
                Yes, we are planning to release a Pro-Version for a small subscription fee, to allow for remote control.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">Is there a way to open the box when you loose your password?</button>
            <div class="faq-answer">
                No. There is no recovery.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">Can I use LockMeBox on Iphone?</button>
            <div class="faq-answer">
                No, but we are planning to release an IOS version soon.
            </div>
        </div>

          <div class="faq-item">
            <button class="faq-question">What happens when your LockMeBox is locked and the battery is empty?</button>
            <div class="faq-answer">
                Your LockMeBox will stay locked until you recharge it.
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