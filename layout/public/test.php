<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous" ></script>
<script src="https://www.google.com/recaptcha/api.js?render=6LdoY3gjAAAAACDLFQE32r5x4x4qnSIIjotAs2eg"></script>



<form id="careerForm" enctype="multipart/form-data" method="POST" action="/ajax/sendCareersForm">
    <input type="hidden" id="jobId" name="jobId" value="1" />
    <input type="hidden" id="captcha_response" name="captcha_response" value="" />
    <div class="form-group">
        <input type="text" class="form-control" id="fullname" name="name" placeholder="الاسم" value="1" required/>
    </div>
    <div class="form-group">
        <input type="email" class="form-control" id="email" name="email" placeholder="البريد الالكتروني" value="1" required/>
    </div>
    <div class="form-group">
        <select id="gender" name="gender" class="form-control" required>
            <option value="1" selected></option>
            <option value="أنثى">أنثى</option>
        </select>
    </div>
    <div class="form-group">
        <select id="education" name="education" class="form-control" required>
            <option value="1" selected></option>
            <option disabled>إختر المرحلة التعليمية</option>
            <option value="المدرسة الثانوية أو أقل">المدرسة الثانوية أو أقل</option>
            <option value="دبلوم (برنامج 2-3 سنوات بعد المدرسة الثانوية)">دبلوم (برنامج 2-3 سنوات بعد المدرسة الثانوية)</option>
            <option value="بكالوريوس">بكالوريوس</option>
            <option value="Masters and above">الماجستير وما فوق</option>
        </select>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" id="nationality" name="nationality" placeholder="إختر الجنسية" value="1" autocomplete="off" required/>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" id="residence" name="residence" placeholder="إختر بلد الإقامة" value="1" autocomplete="off" required/>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" id="mobile" name="mobile" placeholder="الهاتف المحمول" value="1" required/>
    </div>
    <div class="form-group">
        <button id="OpenFileUpload">تحميل السيرة الذاتية</button>
        <input type="file" class="custom-file-input hidden" name='filePath' id="filePath"  accept=".doc,.docx, text/plain, .pdf, .rtf, .png, .jpg, .jpeg, .gif" required>
    </div>
    <!--            <div class="form-group">
                    <div class="div-recaptcha">
                        <div class="g-recaptcha" id="recaptcha" name='captcha_response'  data-sitekey="6LdoY3gjAAAAACDLFQE32r5x4x4qnSIIjotAs2eg" ></div>
                    </div>
                </div>-->
    <div class="form-group">
        <button class="send-career-btn g-recaptcha"  data-sitekey="6LdoY3gjAAAAACDLFQE32r5x4x4qnSIIjotAs2eg" data-callback='sendCareersForm' data-action='submit'>ارسل</button>
        <div class="emailResponse" id="emailResponse">

        </div>
    </div>
</form>


<script>
    $(function () {
        grecaptcha.ready(function() {
            grecaptcha.execute('6LdoY3gjAAAAACDLFQE32r5x4x4qnSIIjotAs2eg', {action:'validate_captcha'})
                .then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                });
        });

    });
    function sendCareersForm() {
        var captcha_response = grecaptcha.getResponse(0);
        $('#captcha_response').val(captcha_response);
        $('#careerForm').submit();
    }

</script>