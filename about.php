<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "About Us | Student Hub";
$active_page = "about";

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1100px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:35px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        
        <h2 style="color:#245B73;margin-top:0 0 5px;">About Student Hub &amp; CHARUSAT</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:25px;">
            Academic Management &bull; Full-Stack Web Development Frameworks (ITUE203) &bull; Semester 3 (2026-27 ODD)
        </p>

        <!-- Project Overview Grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:25px;margin-bottom:30px;">
            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:25px;">
                <h3 style="color:#245B73;margin-top:0;font-size:18px;">About the University &amp; Institute</h3>
                <p style="color:#555;font-size:14px;line-height:1.7;">
                    <strong>Charotar University of Science and Technology (CHARUSAT)</strong> is a premier NAAC 'A+' accredited university located in Changa, Anand, Gujarat.
                </p>
                <p style="color:#555;font-size:14px;line-height:1.7;">
                    The <strong>Chandubhai S Patel Institute of Technology (CSPIT)</strong> is the pioneering engineering constituent of CHARUSAT. The Department of Computer Science &amp; Engineering equips engineers with state-of-the-art software development skills, full-stack frameworks, database modeling, and cybersecurity best practices.
                </p>
            </div>

            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:25px;">
                <h3 style="color:#245B73;margin-top:0;font-size:18px;">Project Specifications</h3>
                <ul style="color:#555;font-size:14px;line-height:1.8;padding-left:20px;">
                    <li><strong>Subject:</strong> Web Development Frameworks (ITUE203)</li>
                    <li><strong>Semester:</strong> 3rd Semester, Academic Year 2026-27</li>
                    <li><strong>Architecture:</strong> Model-View-Controller style PHP &amp; MySQL architecture</li>
                    <li><strong>Frontend:</strong> HTML5 Semantic Elements, CSS3 Grid/Flexbox, Vanilla JS ES6+, AJAX Fetch API</li>
                    <li><strong>Backend:</strong> PHP 8.2 with PDO &amp; MySQLi Prepared Statements, Session RBAC, REST JSON API</li>
                    <li><strong>Security:</strong> bcrypt <code>password_hash()</code>, CSRF validation, session timeout, XSS escaping, SQL injection prevention</li>
                </ul>
            </div>
        </div>

        <!-- Developer Credentials Card -->
        <div style="background:linear-gradient(135deg, #EBF5F9, #D9ECF3);border:1px solid #76B6C9;border-radius:10px;padding:25px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;margin-bottom:30px;">
            <div style="display:flex;align-items:center;gap:20px;">
                <img src="Photo/logo.png" alt="Student Avatar" style="width:75px;height:75px;border-radius:50%;background:white;padding:5px;box-shadow:0 3px 10px rgba(0,0,0,0.15);">
                <div>
                    <h3 style="color:#245B73;margin:0 0 4px;font-size:1.3rem;">Dhruvi Mokani</h3>
                    <p style="margin:0;color:#333;font-size:14px;"><strong>Student ID:</strong> 25CS047 &bull; Candidate for B.Tech CSE</p>
                    <p style="margin:4px 0 0;color:#555;font-size:13px;">Faculty of Technology &amp; Engineering (CSPIT), CHARUSAT</p>
                </div>
            </div>

            <div>
                <a href="contact.php" style="background:#245B73;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;display:inline-block;">
                    Contact Department &rarr;
                </a>
            </div>
        </div>

        <!-- Course Faculty & Mentors (from curriculum PDF) -->
        <h3 style="color:#245B73;margin-top:0;font-size:18px;">Course Faculty Mentors (CSPIT - CE &amp; IT)</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;margin-bottom:25px;font-size:13px;color:#444;">
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Dr. Martin Parmar (CSPIT-CE)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Dr. Mrugendra Rahevar (CSPIT-CE)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Ms. Srushti Gajjar (CSPIT-CSE)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Ms. Vaibhavi Patel (CSPIT-CSE)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Mr. Ravi Patel (CSPIT-IT)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Mr. Dhaval Patel (CSPIT-IT)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Dr. Jalpesh Vasa (CSPIT-AIML)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Ms. Priyanka Padhiyar (DEPSTAR-CE)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Mr. Jay Patel (DEPSTAR-CE)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Mr. Vaibhav Matalia (DEPSTAR-CSE)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Ms. Pooja Singh Chaudhary (DEPSTAR-IT)</div>
            <div style="background:#FAFCFD;border:1px solid #E1EDF2;padding:10px 14px;border-radius:6px;">&bull; Dr. Nikita Bhatt (Head-CE, CSPIT)</div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
