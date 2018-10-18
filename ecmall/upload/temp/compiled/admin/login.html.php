<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>您需要登录后才能使用本功能- Powered by ECMall</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.js'; ?>" charset="utf-8"></script>
        <script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'ecmall.js'; ?>" charset="utf-8"></script>
        <script type="text/javascript" src="templates/js/login.js" charset="utf-8"></script>
        <link href="templates/style/login.css" rel="stylesheet" type="text/css" />

    </head>
    <body>
        <div class="wlogin-body">
            <input class="active-condition" type="hidden" value="<?php echo $this->_var['yunqi_member']['passport_uid']; ?>" />
            <div class="wlogin-header">
                <div class="yunqi-logo"></div>
            </div>
            <div class="center-wrap" <?php if ($this->_var['yunqi_bg']): ?> style="background-image: url('<?php echo $this->_var['yunqi_bg']; ?>')"<?php endif; ?>>
                <div class="form-layout">
                    <div class="header-bg-wrap">
                        <a class="header-bg-710600" target="_blank" href="<?php echo $this->_var['yunqi_ad_link']; ?>"></a>
                    </div>
                    <div class="form">
                        
                        <div class="form-box clearfix">
                            <div class="wlogin-panel fl">
                                <div class="ykd-logo-wrap">
                                    <div class="ykd-logo"></div>
                                    <p class="txt"></p>
                                </div>
                                <div class="wpanel-bd">
                                    
                                    <form class="iframe" method="post">
                                        <div class="forminput">
                                            <div class="Controls">
                                                <svg class="iconphone" width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                                                        <g id="2-copy-2" sketch:type="MSArtboardGroup" transform="translate(-505.000000, -357.000000)" fill="#666">
                                                            <path d="M517.388314,366.868305 C519.068314,366.001784 520.220053,364.252653 520.220053,362.231784 C520.220053,359.350479 517.883966,357.014392 515.002662,357.014392 C512.121357,357.014392 509.78527,359.350479 509.78527,362.231784 C509.78527,364.252653 510.936575,366.001784 512.616575,366.868305 C508.246575,367.938305 505.002662,371.879175 505.002662,376.57961 C505.002662,376.81961 505.197009,377.014392 505.437444,377.014392 C505.677444,377.014392 505.872227,376.81961 505.872227,376.57961 C505.872227,371.537001 509.960053,367.449175 515.002662,367.449175 C520.04527,367.449175 524.133096,371.537001 524.133096,376.57961 C524.133096,376.81961 524.327444,377.014392 524.567879,377.014392 C524.807879,377.014392 525.002662,376.81961 525.002662,376.57961 C525.002662,371.879175 521.758749,367.938305 517.388314,366.868305 L517.388314,366.868305 Z M510.654835,362.231784 C510.654835,359.830479 512.601357,357.883957 515.002662,357.883957 C517.403966,357.883957 519.350488,359.830479 519.350488,362.231784 C519.350488,364.632653 517.403966,366.57961 515.002662,366.57961 C512.601357,366.57961 510.654835,364.632653 510.654835,362.231784 L510.654835,362.231784 Z" id="id" sketch:type="MSShapeGroup"></path>
                                                        </g>
                                                    </g>
                                                </svg>
                                                <input type="text" id="user_name" name="user_name" value="" placeholder="请输入用户名">
                                            </div>

                                            <div class="Controls">
                                                <svg class="iconphone" width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                                                        <g id="2-copy-2" sketch:type="MSArtboardGroup" transform="translate(-505.000000, -407.000000)" fill="#666">
                                                            <path d="M515,418.304324 C514.12782,418.304324 513.421091,418.888119 513.421091,419.608723 C513.421091,419.995004 513.624357,420.341947 513.947394,420.580774 L513.947394,421.782554 C513.947394,422.262857 514.418637,422.652187 515.00003,422.652187 C515.581302,422.652187 516.052667,422.262857 516.052667,421.782554 L516.052667,420.580774 C516.375703,420.341947 516.579,419.995004 516.579,419.608723 C516.57897,418.888119 515.87221,418.304324 515,418.304324 L515,418.304324 L515,418.304324 Z M522.368454,414.391327 L521.315788,414.391327 L521.315788,412.217421 C521.315788,409.335657 518.488418,407 515,407 C511.511582,407 508.684212,409.335657 508.684212,412.217421 L508.684212,414.391327 L507.631576,414.391327 C506.178003,414.391327 505,415.364503 505,416.565234 L505,424.826193 C505,426.026824 506.178003,427 507.631576,427 L522.368424,427 C523.821422,427 525,426.026899 525,424.826193 L525,416.565234 C525.00003,415.364478 523.821422,414.391327 522.368454,414.391327 L522.368454,414.391327 L522.368454,414.391327 Z M515,407.869583 C517.906571,407.869583 520.263152,409.816309 520.263152,412.217396 L520.263152,414.391302 L509.737544,414.391302 L509.737544,412.217396 L509.736848,412.217396 C509.736848,409.816309 512.093459,407.869583 515,407.869583 L515,407.869583 L515,407.869583 Z M523.947364,424.826093 C523.947364,425.546622 523.240604,426.130392 522.368454,426.130392 L507.631606,426.130392 C506.759396,426.130392 506.052667,425.546622 506.052667,424.826093 L506.052667,416.565234 C506.052667,415.84468 506.759426,415.260835 507.631606,415.260835 L522.368454,415.260835 C523.240635,415.260835 523.947364,415.844705 523.947364,416.565234 L523.947364,424.826093 L523.947364,424.826093 L523.947364,424.826093 Z" id="pw" sketch:type="MSShapeGroup"></path>
                                                        </g>
                                                    </g>
                                                </svg>
                                                <input type="password" name="password" id="" value=""  placeholder="请输入密码" >
                                            </div>
                                            <?php if ($this->_var['captcha']): ?>
                                            <div class="Controls" id="captchas_div" style="position:relative;">
                                                <svg class="iconphone" width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                                                        <g id="2-copy-2" sketch:type="MSArtboardGroup" transform="translate(-505.000000, -457.000000)" fill="#666">
                                                            <path d="M514.992364,462.720402 C514.752337,462.720402 514.558125,462.879933 514.558125,463.077076 L514.558125,468.784241 C514.558125,468.981384 514.752337,469.140915 514.992364,469.140915 C515.232364,469.140915 515.426576,468.981384 515.426576,468.784241 L515.426576,463.077076 C515.426576,462.879933 515.232364,462.720402 514.992364,462.720402 L514.992364,462.720402 Z M524.979837,460.500513 C524.421168,460.552589 523.839891,460.580179 523.242853,460.580179 C519.219511,460.580179 515.906875,459.331741 515.474864,457.726607 L515.426603,457.726607 L515.426603,457.369955 C515.426603,457.173147 515.232391,457.013259 514.992391,457.013259 C514.752364,457.013259 514.558152,457.17317 514.558152,457.369955 L514.558152,457.726629 L514.509891,457.726629 C514.077853,459.331763 510.76519,460.580201 506.741821,460.580201 C506.14481,460.580201 505.563533,460.552589 505.004891,460.500536 L505.004891,461.214799 C505.015217,461.215737 505.025462,461.216808 505.035815,461.217746 C505.015408,461.592723 505.004891,461.974732 505.004891,462.363705 C505.004891,470.440558 509.476196,476.988237 514.992391,476.988237 C520.50856,476.988237 524.979864,470.440558 524.979864,462.363705 C524.979864,461.974732 524.969321,461.592723 524.948913,461.217746 C524.959266,461.216808 524.969511,461.215759 524.979864,461.214799 L524.979864,460.500513 L524.979837,460.500513 Z M514.992364,476.274866 C509.956196,476.274866 505.873315,470.046652 505.873315,462.363705 C505.873315,461.994085 505.882962,461.631317 505.901576,461.27529 C506.178125,461.287299 506.458315,461.293638 506.741793,461.293638 C510.588886,461.293638 513.851549,460.163281 514.992364,458.597634 C516.133152,460.163281 519.395842,461.293638 523.242853,461.293638 C523.526386,461.293638 523.806576,461.287299 524.083125,461.27529 C524.101739,461.631317 524.111386,461.994107 524.111386,462.363705 C524.111386,470.046629 520.028533,476.274866 514.992364,476.274866 L514.992364,476.274866 Z M514.992364,469.85433 C514.752337,469.85433 514.558125,470.014196 514.558125,470.211004 L514.558125,470.924375 C514.558125,471.121518 514.752337,471.281094 514.992364,471.281094 C515.232364,471.281094 515.426576,471.121518 515.426576,470.924375 L515.426576,470.211004 C515.426576,470.014196 515.232364,469.85433 514.992364,469.85433 L514.992364,469.85433 Z" id="code" sketch:type="MSShapeGroup"></path>
                                                        </g>
                                                    </g>
                                                </svg>
                                                <input name="captcha" type="text" data-error-msg="验证码不能为空" class="Inp-v" placeholder="验证码" />
                                                <div class="code-img" id="number_div" >
                                                    <img onclick="this.src='index.php?app=captcha&' + Math.round(Math.random()*10000)" style="cursor:pointer;" class="validate" src="index.php?app=captcha&<?php echo $this->_var['random_number']; ?>"/>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="Controls Justify">
                                            <button type="submit" class="Btn-a">登录</button>
                                        </div>
                                        <div class="Bside">
                                            <input type="checkbox" value="自动登录"><label>自动登录</label>
                                            <a class="forget" target="_blank" href="<?php echo $this->_var['site_url']; ?>/index.php?app=find_password">忘记密码？</a>
                                            <a class="go-index" target="_blank" href="<?php echo $this->_var['site_url']; ?>">去店铺首页>></a>
                                        </div>
                                    </form>
                                </div>
                            </div>
<!--                             <div class="commnt ohter-ecmall fr"> -->
<!--                                 <div class="commnt-header">其他登录方式</div> -->
<!--                                 <div class="commnt-icon"> -->
<!--                                     <div class="commnt-icon-img"></div> -->
<!--                                     <p>云起账号</p> -->
<!--                                 </div> -->
<!--                             </div> -->
                        </div>
                        
                        <div class="yunqi-wrap clearfix">
                            <div class="wlogin-panel yunqi-wlogin-panel fr">
                                <div class="ykd-logo-wrap">
                                    <div class="ykd-logo"></div>
                                    <p class="txt">云起登录</p>
                                </div>
                                <div class="wpanel-bd">
                                    <iframe src="<?php echo $this->_var['iframe_url']; ?>" width="100%" frameborder="0" height="255px" class="Login-frame" allowtransparency></iframe>
                                </div>
                            </div>
                            <div class="commnt ohter-yunqi fl">
                                <div class="commnt-header">其他登录方式</div>
                                <div class="commnt-icon">
                                    <div class="commnt-icon-img"></div>
                                    <p>ECMALL账号</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p class="wlogo-copyright">&copy;&nbsp;&nbsp;2013-2015&nbsp;ShopEx,Inc.All&nbsp;rights&nbsp;reserverd.</p>

            
<!--             <div class="mask-black" id="CMask"> -->
<!--                 <div class="panel-hint panel-icloud" id="panelCloud"> -->
<!--                     <div class="panel-cross"><span class="panel-close">Ｘ</span></div> -->
<!--                     <div class="panel-title"> -->
<!--                         <span class="tit">您需要激活系统</span> -->
<!--                         <p>用云起账号激活您的系统，享受物流查询，天工收银，手机短信等更多应用和服务</p> -->
<!--                     </div> -->
<!--                     <div class="panel-left"> -->
<!--                         <span>没有云起账号吗？</span> -->
<!--                         <p>点击下列按钮一步完成注册激活！</p> -->
<!--                         <a href="https://account.shopex.cn/reg?refer=yunqi_ecshop" target="_blank" class="btn btn-yellow">免费注册云起账号</a> -->
<!--                     </div> -->
<!--                     <div class="panel-right"> -->
<!--                         <h5 class="logo">云起</h5> -->
<!--                         <p>正在激活中</p> -->
<!--                         <iframe src="<?php echo $this->_var['activate_iframe_url']; ?>" frameborder="0" id="CFrame"></iframe> -->
<!--                         <div class="cloud-passw"> -->
<!--                             <a target="_blank" href="https://account.shopex.cn/forget?">忘记密码？</a> -->
<!--                         </div> -->
<!--                     </div> -->
<!--                 </div> -->
<!--             </div> -->
        </div>
    </body>
</html>
