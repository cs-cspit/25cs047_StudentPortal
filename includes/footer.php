<footer style="background:linear-gradient(135deg,#1C4658,#245B73);color:white;padding:35px 20px 20px;margin-top:auto;font-family:'Segoe UI',sans-serif;">
    <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:25px;text-align:left;">
        <div>
            <h3 style="color:#76B6C9;margin-top:0;font-size:1.2rem;">Student Hub</h3>
            <p style="font-size:13px;color:#D3E6EE;line-height:1.6;">
                Centralized academic management and student services portal for Charotar University of Science and Technology (CHARUSAT). Providing integrated academic delivery, attendance monitoring, examination results, and campus notifications.
            </p>
            <p style="font-size:12px;color:#A9CBD8;line-height:1.6;">
                Chandubhai S Patel Institute of Technology (CSPIT)<br>
                CHARUSAT Campus, Changa, Anand, Gujarat - 388421
            </p>
        </div>

        <div>
            <h4 style="color:#76B6C9;margin-top:0;font-size:1.05rem;">Academic Modules</h4>
            <ul style="list-style:none;padding-left:0;font-size:13px;line-height:2.2;">
                <li><a href="dashboard.php" style="color:#D3E6EE;text-decoration:none;">&bull; Student Dashboard</a></li>
                <li><a href="attendance.php" style="color:#D3E6EE;text-decoration:none;">&bull; Subject-wise Attendance</a></li>
                <li><a href="timetable.php" style="color:#D3E6EE;text-decoration:none;">&bull; Weekly Class Timetable</a></li>
                <li><a href="assignment.php" style="color:#D3E6EE;text-decoration:none;">&bull; Assignments &amp; Submissions</a></li>
                <li><a href="result.php" style="color:#D3E6EE;text-decoration:none;">&bull; Semester Results &amp; SGPA</a></li>
                <li><a href="fees.php" style="color:#D3E6EE;text-decoration:none;">&bull; Fees &amp; Financial Ledger</a></li>
            </ul>
        </div>

        <div>
            <h4 style="color:#76B6C9;margin-top:0;font-size:1.05rem;">Campus Services &amp; Life</h4>
            <ul style="list-style:none;padding-left:0;font-size:13px;line-height:2.2;">
                <li><a href="events.php" style="color:#D3E6EE;text-decoration:none;">&bull; Campus Events &amp; Hackathons</a></li>
                <li><a href="announcement.php" style="color:#D3E6EE;text-decoration:none;">&bull; University Circulars &amp; Notices</a></li>
                <li><a href="faq.php" style="color:#D3E6EE;text-decoration:none;">&bull; Student Helpdesk &amp; FAQs</a></li>
                <li><a href="feedback.php" style="color:#D3E6EE;text-decoration:none;">&bull; Academic Feedback System</a></li>
                <li><a href="contact.php" style="color:#D3E6EE;text-decoration:none;">&bull; Contact Administration</a></li>
                <li><a href="about.php" style="color:#D3E6EE;text-decoration:none;">&bull; About CSPIT CHARUSAT</a></li>
            </ul>
        </div>

        <div>
            <h4 style="color:#76B6C9;margin-top:0;font-size:1.05rem;">Student Administration</h4>
            <p style="font-size:13px;color:#D3E6EE;margin-bottom:8px;line-height:1.6;">
                <strong>Developer:</strong> Dhruvi Mokani<br>
                <strong>Student ID:</strong> 25CS047<br>
                <strong>Program:</strong> B.Tech Computer Science &amp; Engineering<br>
                <strong>Department:</strong> Department of Computer Science &amp; Engineering<br>
                <strong>Institute:</strong> CSPIT, CHARUSAT University
            </p>
            <div style="margin-top:12px;">
                <span style="display:inline-block;background:rgba(255,255,255,0.15);color:#E2F1F8;padding:6px 12px;border-radius:4px;font-size:12px;font-weight:600;">
                    &bull; Production Release v1.0
                </span>
            </div>
        </div>
    </div>

    <div style="max-width:1200px;margin:25px auto 0;border-top:1px solid rgba(255,255,255,0.15);padding-top:15px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;font-size:12px;color:#A9CBD8;">
        <div>
            &copy; 2026 Student Hub &bull; All Rights Reserved &bull; Faculty of Technology &amp; Engineering, CHARUSAT
        </div>
        <div>
            Powered by Semantic HTML5, CSS Grid/Flexbox, ES6+ JS, PHP 8 &amp; MariaDB
        </div>
    </div>
</footer>

<!-- Reusable UI Scripts & Theme Persistence -->
<script>
// Hamburger mobile menu toggle
function toggleMenu() {
    var nav = document.getElementById("navbar");
    if (nav) nav.classList.toggle("show");
}

// Light / Dark Theme Switcher with LocalStorage (Practical 4 requirement)
function toggleTheme() {
    document.body.classList.toggle("dark");
    var isDark = document.body.classList.contains("dark");
    localStorage.setItem("studenthub_theme", isDark ? "dark" : "light");
    updateThemeButton(isDark);
}

function updateThemeButton(isDark) {
    var btn = document.getElementById("themeBtn");
    if (btn) {
        btn.innerHTML = isDark ? "&#9728; Light" : "&#9790; Dark";
    }
}

// Restore saved theme on page load (Practical 4 requirement)
(function() {
    var savedTheme = localStorage.getItem("studenthub_theme");
    if (savedTheme === "dark") {
        document.body.classList.add("dark");
        updateThemeButton(true);
    }
})();
</script>
</body>
</html>
