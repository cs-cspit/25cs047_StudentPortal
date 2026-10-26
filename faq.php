<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "Frequently Asked Questions | Student Hub";
$active_page = "faq";

// Load 15 FAQs from faqs.json (Practical 6 dataset)
$faqs_file = __DIR__ . '/faqs.json';
$faqs = [];
if (file_exists($faqs_file)) {
    $faqs = json_decode(file_get_contents($faqs_file), true);
    if (!is_array($faqs)) $faqs = [];
}

$question_submitted = false;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ask_question"])) {
    $q_email = trim($_POST["q_email"] ?? "");
    $q_text  = trim($_POST["q_text"] ?? "");
    if (!empty($q_email) && !empty($q_text)) {
        // Save to contact_messages
        $stmt = @mysqli_prepare($conn, "INSERT INTO contact_messages (name, email, message) VALUES ('FAQ Inquiry', ?, ?)");
        if ($stmt) {
            @mysqli_stmt_bind_param($stmt, "ss", $q_email, $q_text);
            @mysqli_stmt_execute($stmt);
            @mysqli_stmt_close($stmt);
        }
        $question_submitted = true;
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:950px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        
        <h2 style="color:#245B73;margin-top:0 0 5px;">Frequently Asked Questions (FAQ)</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:20px;">
            Find immediate answers regarding logins, attendance rules, results, fees, events, and portal security.
        </p>

        <!-- Live Instant Search (Practical 4 & 6) -->
        <div style="margin-bottom:25px;">
            <input type="text" id="faqSearchInput" onkeyup="filterFaqs()" placeholder="Type to filter FAQs dynamically in real-time..." style="width:100%;padding:12px 16px;border:1px solid #76B6C9;border-radius:6px;box-sizing:border-box;font-size:14px;background:#F9FBFC;">
        </div>

        <!-- Collapsible FAQ List (Practical 4) -->
        <div id="faqContainer" style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($faqs as $i => $item): ?>
                <div class="faq-item" style="border:1px solid #D5E5ED;border-radius:8px;overflow:hidden;background:#FAFCFD;">
                    <button type="button" onclick="toggleFaq('faq_ans_<?php echo $i; ?>')" style="width:100%;padding:14px 18px;text-align:left;background:#F4F9FB;border:none;border-bottom:1px solid #E1EDF2;cursor:pointer;font-weight:600;color:#245B73;font-size:15px;display:flex;justify-content:space-between;align-items:center;">
                        <span class="faq-question">Q<?php echo ($i+1); ?>: <?php echo htmlspecialchars($item['question']); ?></span>
                        <span id="faq_icon_<?php echo $i; ?>" style="font-size:16px;font-weight:bold;color:#76B6C9;">+</span>
                    </button>
                    <div id="faq_ans_<?php echo $i; ?>" class="faq-answer" style="display:none;padding:15px 18px;font-size:14px;color:#4B5563;line-height:1.6;background:white;">
                        <span style="display:inline-block;background:#E1F0F5;color:#1F546B;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;margin-bottom:8px;">
                            <?php echo htmlspecialchars($item['category']); ?>
                        </span>
                        <div><?php echo htmlspecialchars($item['answer']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Ask Question Form -->
        <div style="margin-top:35px;background:#F4F9FB;padding:25px;border-radius:8px;border:1px solid #D5E5ED;">
            <h3 style="color:#245B73;margin-top:0;font-size:18px;">Still Have a Question?</h3>
            <p style="font-size:13px;color:#666;margin-bottom:15px;">Send your question directly to our student support desk:</p>

            <?php if ($question_submitted): ?>
                <div style="background:#DEF7EC;color:#03543F;padding:10px 15px;border-radius:6px;font-weight:bold;font-size:13px;margin-bottom:15px;">
                    &#10004; Thank you! Your question has been submitted to the support desk.
                </div>
            <?php endif; ?>

            <form method="POST" action="faq.php">
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:4px;font-size:13px;">Your Email Address</label>
                    <input type="email" name="q_email" required placeholder="student@charusat.edu.in" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:4px;font-size:13px;">Your Question</label>
                    <textarea name="q_text" rows="3" required placeholder="Type your specific query here..." style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;font-family:inherit;"></textarea>
                </div>
                <button type="submit" name="ask_question" style="background:#245B73;color:white;border:none;padding:10px 20px;border-radius:6px;font-weight:bold;cursor:pointer;">
                    Submit Question
                </button>
            </form>
        </div>

    </div>
</main>

<script>
// Practical 4 Collapsible FAQ logic
function toggleFaq(ansId) {
    var ans = document.getElementById(ansId);
    var iconIndex = ansId.replace('faq_ans_', '');
    var icon = document.getElementById('faq_icon_' + iconIndex);

    if (ans.style.display === "none" || ans.style.display === "") {
        ans.style.display = "block";
        if (icon) icon.textContent = "-";
    } else {
        ans.style.display = "none";
        if (icon) icon.textContent = "+";
    }
}

// Live Search Filter across all FAQs
function filterFaqs() {
    var query = document.getElementById("faqSearchInput").value.toLowerCase();
    var items = document.querySelectorAll(".faq-item");

    items.forEach(function(item) {
        var text = item.textContent.toLowerCase();
        if (text.includes(query)) {
            item.style.display = "block";
        } else {
            item.style.display = "none";
        }
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
