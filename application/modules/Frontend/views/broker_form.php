<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Investment Form</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Poppins',sans-serif;

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    padding:40px 20px;

    background:
    linear-gradient(
    135deg,
    #0f172a,
    #1e3a8a,
    #312e81
    );

    overflow:hidden;

    position:relative;
}

/* ========================= */
/* BACKGROUND EFFECT */
/* ========================= */

body::before{

    content:'';

    position:absolute;

    width:700px;

    height:700px;

    background:rgba(255,255,255,0.05);

    border-radius:50%;

    top:-250px;

    right:-180px;

    filter:blur(10px);
}

body::after{

    content:'';

    position:absolute;

    width:500px;

    height:500px;

    background:rgba(255,255,255,0.05);

    border-radius:50%;

    bottom:-180px;

    left:-120px;

    filter:blur(10px);
}

/* ========================= */
/* FORM CONTAINER */
/* ========================= */

.form-container{

    width:100%;

    max-width:520px;

    padding:45px;

    border-radius:30px;

    background:rgba(255,255,255,0.05);

    border:1px solid rgba(255,255,255,0.1);

    backdrop-filter:blur(20px);

    -webkit-backdrop-filter:blur(20px);

    box-shadow:
    0 10px 40px rgba(0,0,0,0.3);

    position:relative;

    z-index:10;
}

/* ========================= */
/* HEADER */
/* ========================= */

.form-header{

    text-align:center;

    margin-bottom:35px;
}

.logo{

    width:75px;

    height:75px;

    border-radius:20px;

    margin:auto;

    margin-bottom:20px;

    border:2px solid #ffffff;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#ffffff;

    font-size:32px;

    font-weight:700;
}

.form-header h1{

    color:#ffffff;

    font-size:32px;

    margin-bottom:10px;
}

.form-header p{

    color:rgba(255,255,255,0.75);

    font-size:15px;

    line-height:1.6;
}

/* ========================= */
/* FORM GROUP */
/* ========================= */

.form-group{

    margin-bottom:22px;
}

.form-group label{

    display:block;

    margin-bottom:10px;

    color:#ffffff;

    font-size:14px;

    font-weight:500;
}

/* ========================= */
/* INPUTS */
/* ========================= */

.input-box{
    position:relative;
}

.input-box input,
.input-box select,
.input-box textarea{

    width:100%;

    padding:17px 18px;

    border-radius:18px;

    border:2px solid #ffffff;

    background:transparent;

    color:#ffffff;

    font-size:15px;

    outline:none;

    transition:0.3s;
}

.input-box textarea{

    min-height:120px;

    resize:none;
}

/* PLACEHOLDER */

.input-box input::placeholder,
.input-box textarea::placeholder{

    color:rgba(255,255,255,0.8);
}

/* SELECT */

.input-box select{

    color:#ffffff;
}

.input-box option{

    color:#000000;

    background:#ffffff;
}

/* FOCUS */

.input-box input:focus,
.input-box select:focus,
.input-box textarea:focus{

    background:rgba(255,255,255,0.05);

    box-shadow:
    0 0 15px rgba(255,255,255,0.2);
}

/* ========================= */
/* CHECKBOX */
/* ========================= */

.checkbox-grid{

    display:grid;

    grid-template-columns:repeat(2,1fr);

    gap:12px;
}

.checkbox-item{

    border:2px solid #ffffff;

    border-radius:16px;

    padding:14px;

    color:#ffffff;

    background:transparent;

    cursor:pointer;

    transition:0.3s;
}

.checkbox-item:hover{

    background:rgba(255,255,255,0.08);

    transform:translateY(-2px);
}

.checkbox-item input{

    margin-right:8px;
}

/* ========================= */
/* BUTTON */
/* ========================= */

.submit-btn{

    width:100%;

    padding:18px;

    border:none;

    border-radius:18px;

    background:#ffffff;

    color:#1e3a8a;

    font-size:17px;

    font-weight:700;

    cursor:pointer;

    transition:0.3s;

    margin-top:10px;
}

.submit-btn:hover{

    transform:translateY(-3px);

    background:#f1f5f9;
}

/* ========================= */
/* FOOTER */
/* ========================= */

.form-footer{

    text-align:center;

    margin-top:25px;

    color:rgba(255,255,255,0.7);

    font-size:14px;
}

.form-footer a{

    color:#ffffff;

    font-weight:600;

    text-decoration:none;
}

/* ========================= */
/* RESPONSIVE */
/* ========================= */

@media(max-width:600px){

    .form-container{

        padding:30px 22px;
    }

    .checkbox-grid{

        grid-template-columns:1fr;
    }

    .form-header h1{

        font-size:26px;
    }
}

</style>
</head>

<body>

<div class="form-container">

    <!-- HEADER -->
    <div class="form-header">

        <div class="logo">
            I
        </div>

        <h1>Join Investors</h1>

        <p>
            Connect with startups and investment opportunities worldwide.
        </p>

    </div>

    <!-- FORM -->
    <form>

        <!-- NAME -->
        <div class="form-group">

            <label>Full Name</label>

            <div class="input-box">

                <input
                    type="text"
                    placeholder="Enter your full name"
                >

            </div>

        </div>

        <!-- EMAIL -->
        <div class="form-group">

            <label>Email Address</label>

            <div class="input-box">

                <input
                    type="email"
                    placeholder="example@email.com"
                >

            </div>

        </div>

        <!-- PHONE -->
        <div class="form-group">

            <label>Phone Number</label>

            <div class="input-box">

                <input
                    type="text"
                    placeholder="+257 ..."
                >

            </div>

        </div>

        <!-- USER TYPE -->
        <div class="form-group">

            <label>User Type</label>

            <div class="input-box">

                <select>

                    <option>Investor</option>

                    <option>Startup</option>

                    <option>Entrepreneur</option>

                    <option>Company</option>

                </select>

            </div>

        </div>

        <!-- INTEREST -->
        <div class="form-group">

            <label>Investment Interests</label>

            <div class="checkbox-grid">

                <label class="checkbox-item">

                    <input type="checkbox">

                    Real Estate

                </label>

                <label class="checkbox-item">

                    <input type="checkbox">

                    Technology

                </label>

                <label class="checkbox-item">

                    <input type="checkbox">

                    Agriculture

                </label>

                <label class="checkbox-item">

                    <input type="checkbox">

                    E-commerce

                </label>

            </div>

        </div>

        <!-- DESCRIPTION -->
        <div class="form-group">

            <label>Description</label>

            <div class="input-box">

                <textarea
                    placeholder="Describe your investment goals..."
                ></textarea>

            </div>

        </div>

        <!-- BUTTON -->
        <button class="submit-btn">

            Create Account

        </button>

    </form>

    <!-- FOOTER -->
    <div class="form-footer">

        Already have an account ?

        <a href="#">
            Login
        </a>

    </div>

</div>

</body>
</html>