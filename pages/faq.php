<?php
// Include any necessary header files or database connections here
require_once 'config.php';
require_once 'functions.php';

// Define FAQs in Bengali
$faqs = [
    [
        'question' => 'কে রক্ত দান করতে পারে?',
        'answer' => 'সাধারণত, ১৮-৬৫ বছর বয়সী, কমপক্ষে ৫০ কেজি ওজনের এবং সুস্থ ব্যক্তিরা রক্ত দান করতে পারেন। তবে, নির্দিষ্ট যোগ্যতার মানদণ্ড ভিন্ন হতে পারে।'
    ],
    [
        'question' => 'কত ঘন ঘন আমি রক্ত দান করতে পারি?',
        'answer' => 'বেশিরভাগ সুস্থ ব্যক্তি প্রতি ৫৬ দিন (৮ সপ্তাহ) অন্তর সম্পূর্ণ রক্ত দান করতে পারেন।'
    ],
    [
        'question' => 'রক্তদান কি নিরাপদ?',
        'answer' => 'হ্যাঁ, রক্তদান খুবই নিরাপদ। ব্যবহৃত সমস্ত সরঞ্জাম জীবাণুমুক্ত এবং একবার ব্যবহারের পর ফেলে দেওয়া হয়।'
    ],
    [
        'question' => 'রক্তদান করতে কত সময় লাগে?',
        'answer' => 'প্রকৃত রক্তদান সাধারণত ৮-১০ মিনিট সময় নেয়। তবে, নিবন্ধন এবং স্বাস্থ্য পরীক্ষা সহ সম্পূর্ণ প্রক্রিয়াটি প্রায় এক ঘণ্টা সময় নিতে পারে।'
    ],
    [
        'question' =>'রক্তদান করতে কত টাকা লাগে?',
        'answer' =>'আমাদের সাইটে রক্তদানের জন্য কোনো টাকা নেওয়া হয় না। রক্তদান সম্পূর্ণরূপে বিনামূল্যে করা হয়।'
    ]
];

?>

<div class="container mt-5">
    <h1 class="mb-4">প্রায়শই জিজ্ঞাসিত প্রশ্নাবলী</h1>

    <div class="accordion" id="faqAccordion">
        <?php foreach ($faqs as $index => $faq): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                        <?php echo $faq['question']; ?>
                    </button>
                </h2>
                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p><?php echo $faq['answer']; ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
