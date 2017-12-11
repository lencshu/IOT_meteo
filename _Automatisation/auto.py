# -*- coding: utf-8 -*- 
#pip install gittle
#git config --global core.autocrlf false
#

import os
import os.path
import ConfigParser
import time
import subprocess
import datetime
from bs4 import BeautifulSoup
import re
from gittle import Gittle

#初始化变量
currentDir = os.getcwd()
parentDir = os.path.abspath(os.path.join(currentDir, os.pardir))
iniExiste = os.path.exists(currentDir+"\\auto.ini")
conf = ConfigParser.ConfigParser()

commentContent = raw_input("Commit the update [Bug Fixed]: ") or r"Bug Fixed"
commentContent = "\"" + commentContent + "\""

htmlReplaceResize="///" + parentDir.replace('\\','/') + "/"
htmlReplaceResize=htmlReplaceResize.replace(':/','://')
# ///C://Users/lencs/Desktop/Drive/
htmlReplaceParentDir = parentDir + "\\"
#C:\\Users\\lencs\\Desktop\\Drive\\


numberPNGchanged=0

if iniExiste:
	conf.read('auto.ini')       # 文件路径
	htmlName = conf.get("HTML", "HtmlName") # 获取指定section 的option值
	sidebarSize = conf.get("HTML", "SidebarSize")
	mainpageSize = conf.get("HTML", "MainpageSize")
	deleteOriginHtml = conf.get("HTML", "DeleteOriginHtml")
	multiMediaDir = conf.get("MultiMedia", "DirName") # 获取指定section 的option值
	lastChangeTime = conf.get("MultiMedia", "LastChangeTime")

else :
	conf.read('auto.ini')
	conf.add_section("MultiMedia") # 增加section
	conf.add_section("HTML") 
	conf.add_section("TimeTag")
	htmlName = raw_input("Original Html Name: ")
	multiMediaDir = raw_input("The name of [MultiMedia] directory: ") or "MultiMedia"
	sidebarSize = raw_input("Side Bar Size (%) [30]: ") or "30"
	sidebarSize = "width:" + sidebarSize + "%"
	mainpageSize = raw_input("Main Page Size (%) [68]: ") or "68"
	mainpageSize = mainpageSize + "%;"
	deleteOriginHtml = raw_input("Want To Delete Origin Html(Yes:1/[No:0]): ") or "0"
	lastChangeTime = raw_input("Last Changed time of All PNGs Files [0]: ") or "0"

	conf.set("HTML", "HtmlName",htmlName) # 增加指定section 的option
	conf.set("HTML", "SidebarSize",sidebarSize)
	conf.set("HTML", "MainpageSize",mainpageSize)
	conf.set("HTML", "DeleteOriginHtml",deleteOriginHtml)
	conf.set("TimeTag", "IniModifiedTime",deleteOriginHtml)
	conf.set("MultiMedia", "DirName",multiMediaDir) # 获取指定section 的option值
	# conf.set("MultiMedia", "LastChangeTime",lastChangeTime )
	timeTag=time.strftime("%Y-%m-%d %H:%M:%S %a", time.localtime())
	conf.set("TimeTag", "IniModifiedTime",timeTag)
	conf.write(open('auto.ini', 'w'))

##
## 缩小PNG图片
##
# pngChangedSigne=0
args = " --force --verbose --quality=45-80 --ext=.png"
mediaFolder = parentDir +"\\" + multiMediaDir
lastChangeTime=float(lastChangeTime)
print lastChangeTime
refTimeAfterAll = lastChangeTime
ChangedTime=0
pngChangedSigne=0

def iniConfPng(t):
	conf.read('auto.ini')
	conf.set("MultiMedia", "LastChangeTime",t)
	timeTag=time.strftime("%Y-%m-%d %H:%M:%S %a", time.localtime())
	conf.set("TimeTag", "IniModifiedTime",timeTag)
	conf.write(open('auto.ini', 'w'))


for file in os.listdir(mediaFolder):
	if file.endswith(".png"):
		pngPath = os.path.join(mediaFolder, file)
		fileTime=os.path.getmtime(pngPath)
		# print fileTime,lastChangeTime,refTimeAfterAll #Debug
		if (lastChangeTime+0.01 < fileTime) and (lastChangeTime != 0) :
			# print fileTime,lastChangeTime,refTimeAfterAll #Debug
			pngtoModify = os.path.abspath(pngPath)
			numberPNGchanged+=1
			print numberPNGchanged
			pn = subprocess.Popen("pngquant.exe " + pngtoModify + args, shell = True, stdout = subprocess.PIPE, stderr = subprocess.PIPE )
			pn.wait()
			pngChangedSigne=1
			refTimeAfterAll=os.path.getmtime(pngPath)
			# print refTimeAfterAll #debug
			# (out, error) = pn.communicate()
			# if str(error):
			# 	print "Error : " + error
			# if str(out):
			# 	print "out : " + str(out)
			# refTimeAfterAll=os.path.getmtime(pngPath)
			# print pngPath #Debug
			# print refTimeAfterAll #debug
		elif lastChangeTime == 0:
			if ChangedTime < fileTime:
				ChangedTime = fileTime
			pngChangedSigne=2

if pngChangedSigne==1:
	iniConfPng(refTimeAfterAll)
	print "==== PNGs newly modified date updated "
if pngChangedSigne==2:
	iniConfPng(ChangedTime)
	print "==== PNGs initialised modified date created "

##
## 网页重制并加入sidebar
##

htmlExiste = os.path.exists(parentDir+"\\index.html")
htmlOriginalPath = parentDir + "\\" + htmlName
indexPath = parentDir + "\\index.html"

if htmlExiste:
	os.remove(indexPath)
#========change the html file name======
html = open(htmlOriginalPath,"r+")

soup = BeautifulSoup(html, "html.parser")
# Move the content of divtoc to divto
divto = soup.find("div", class_="to")
divtoc = soup.find("div", class_="toc")
if divto!=None or divto!="": 
    divto.append(divtoc)
#rename the class name of divto to tod
divto.div['class'] = 'tod'

#Add Sidebar
html = soup.prettify("utf-8")
keyword="outline: 1300px solid #fff;"
post = html.find(keyword)
if post != -1:
#Add Sidebar properity
    html = html[:post+len(keyword)]+"float:right;padding-left:10px;width:"+ mainpageSize +html[post+len(keyword):]
    html = html.replace('width:36%', sidebarSize)
    print "==== Sidebar added "
#Change to related directory
    html = html.replace(htmlReplaceResize, '')
    html = html.replace(htmlReplaceParentDir, '')
    print "==== Related directory changed "
#delete sensitive infos
    html=re.sub(r'%mdp%.*%/mdp%', 'X*X*X*X*X*X',html)
    html=re.sub(r'<span class=".*">.*%</span><span class=".*">mdp</span><span class=".*">%.*</span>.*<span class=".*">.*%/</span><span class=".*">mdp</span><span class=".*">%.*</span>', 'X*X*X*X*X*X',html)
    print "==== Sensitive infos deleted "
#Lazy loading image
    html = html.replace('img alt=\"\" src', 'img class=\"lazyload\" alt=\"\" data-src')
#Lazy loading audio
    html = html.replace('audio con', 'audio class=\"lazyload\" data-poster=\"\" preload=\"none\" con')
#Lazyload javascript initialising
    html = html.replace('<head>', '<head>\n<script type=\"text/javascript\">/*! lazysizes - v4.0.1 */!function(a,b){var c=b(a,a.document);a.lazySizes=c,\"object\"==typeof module&&module.exports&&(module.exports=c)}(window,function(a,b){\"use strict\";if(b.getElementsByClassName){var c,d,e=b.documentElement,f=a.Date,g=a.HTMLPictureElement,h=\"addEventListener\",i=\"getAttribute\",j=a[h],k=a.setTimeout,l=a.requestAnimationFrame||k,m=a.requestIdleCallback,n=/^picture$/i,o=[\"load\",\"error\",\"lazyincluded\",\"_lazyloaded\"],p={},q=Array.prototype.forEach,r=function(a,b){return p[b]||(p[b]=new RegExp(\"(\\\\s|^)\"+b+\"(\\\\s|$)\")),p[b].test(a[i](\"class\")||\"\")&&p[b]},s=function(a,b){r(a,b)||a.setAttribute(\"class\",(a[i](\"class\")||\"\").trim()+\" \"+b)},t=function(a,b){var c;(c=r(a,b))&&a.setAttribute(\"class\",(a[i](\"class\")||\"\").replace(c,\" \"))},u=function(a,b,c){var d=c?h:\"removeEventListener\";c&&u(a,b),o.forEach(function(c){a[d](c,b)})},v=function(a,d,e,f,g){var h=b.createEvent(\"CustomEvent\");return e||(e={}),e.instance=c,h.initCustomEvent(d,!f,!g,e),a.dispatchEvent(h),h},w=function(b,c){var e;!g&&(e=a.picturefill||d.pf)?e({reevaluate:!0,elements:[b]}):c&&c.src&&(b.src=c.src)},x=function(a,b){return(getComputedStyle(a,null)||{})[b]},y=function(a,b,c){for(c=c||a.offsetWidth;c<d.minSize&&b&&!a._lazysizesWidth;)c=b.offsetWidth,b=b.parentNode;return c},z=function(){var a,c,d=[],e=[],f=d,g=function(){var b=f;for(f=d.length?e:d,a=!0,c=!1;b.length;)b.shift()();a=!1},h=function(d,e){a&&!e?d.apply(this,arguments):(f.push(d),c||(c=!0,(b.hidden?k:l)(g)))};return h._lsFlush=g,h}(),A=function(a,b){return b?function(){z(a)}:function(){var b=this,c=arguments;z(function(){a.apply(b,c)})}},B=function(a){var b,c=0,e=125,g=d.ricTimeout,h=function(){b=!1,c=f.now(),a()},i=m&&d.ricTimeout?function(){m(h,{timeout:g}),g!==d.ricTimeout&&(g=d.ricTimeout)}:A(function(){k(h)},!0);return function(a){var d;(a=a===!0)&&(g=33),b||(b=!0,d=e-(f.now()-c),0>d&&(d=0),a||9>d&&m?i():k(i,d))}},C=function(a){var b,c,d=99,e=function(){b=null,a()},g=function(){var a=f.now()-c;d>a?k(g,d-a):(m||e)(e)};return function(){c=f.now(),b||(b=k(g,d))}};!function(){var b,c={lazyClass:\"lazyload\",loadedClass:\"lazyloaded\",loadingClass:\"lazyloading\",preloadClass:\"lazypreload\",errorClass:\"lazyerror\",autosizesClass:\"lazyautosizes\",srcAttr:\"data-src\",srcsetAttr:\"data-srcset\",sizesAttr:\"data-sizes\",minSize:40,customMedia:{},init:!0,expFactor:1.5,hFac:.8,loadMode:2,loadHidden:!0,ricTimeout:300};d=a.lazySizesConfig||a.lazysizesConfig||{};for(b in c)b in d||(d[b]=c[b]);a.lazySizesConfig=d,k(function(){d.init&&F()})}();var D=function(){var g,l,m,o,p,y,D,F,G,H,I,J,K,L,M=/^img$/i,N=/^iframe$/i,O=\"onscroll\"in a&&!/glebot/.test(navigator.userAgent),P=0,Q=0,R=0,S=-1,T=function(a){R--,a&&a.target&&u(a.target,T),(!a||0>R||!a.target)&&(R=0)},U=function(a,c){var d,f=a,g=\"hidden\"==x(b.body,\"visibility\")||\"hidden\"!=x(a,\"visibility\");for(F-=c,I+=c,G-=c,H+=c;g&&(f=f.offsetParent)&&f!=b.body&&f!=e;)g=(x(f,\"opacity\")||1)>0,g&&\"visible\"!=x(f,\"overflow\")&&(d=f.getBoundingClientRect(),g=H>d.left&&G<d.right&&I>d.top-1&&F<d.bottom+1);return g},V=function(){var a,f,h,j,k,m,n,p,q,r=c.elements;if((o=d.loadMode)&&8>R&&(a=r.length)){f=0,S++,null==K&&(\"expand\"in d||(d.expand=e.clientHeight>500&&e.clientWidth>500?500:370),J=d.expand,K=J*d.expFactor),K>Q&&1>R&&S>2&&o>2&&!b.hidden?(Q=K,S=0):Q=o>1&&S>1&&6>R?J:P;for(;a>f;f++)if(r[f]&&!r[f]._lazyRace)if(O)if((p=r[f][i](\"data-expand\"))&&(m=1*p)||(m=Q),q!==m&&(y=innerWidth+m*L,D=innerHeight+m,n=-1*m,q=m),h=r[f].getBoundingClientRect(),(I=h.bottom)>=n&&(F=h.top)<=D&&(H=h.right)>=n*L&&(G=h.left)<=y&&(I||H||G||F)&&(d.loadHidden||\"hidden\"!=x(r[f],\"visibility\"))&&(l&&3>R&&!p&&(3>o||4>S)||U(r[f],m))){if(ba(r[f]),k=!0,R>9)break}else!k&&l&&!j&&4>R&&4>S&&o>2&&(g[0]||d.preloadAfterLoad)&&(g[0]||!p&&(I||H||G||F||\"auto\"!=r[f][i](d.sizesAttr)))&&(j=g[0]||r[f]);else ba(r[f]);j&&!k&&ba(j)}},W=B(V),X=function(a){s(a.target,d.loadedClass),t(a.target,d.loadingClass),u(a.target,Z),v(a.target,\"lazyloaded\")},Y=A(X),Z=function(a){Y({target:a.target})},$=function(a,b){try{a.contentWindow.location.replace(b)}catch(c){a.src=b}},_=function(a){var b,c=a[i](d.srcsetAttr);(b=d.customMedia[a[i](\"data-media\")||a[i](\"media\")])&&a.setAttribute(\"media\",b),c&&a.setAttribute(\"srcset\",c)},aa=A(function(a,b,c,e,f){var g,h,j,l,o,p;(o=v(a,\"lazybeforeunveil\",b)).defaultPrevented||(e&&(c?s(a,d.autosizesClass):a.setAttribute(\"sizes\",e)),h=a[i](d.srcsetAttr),g=a[i](d.srcAttr),f&&(j=a.parentNode,l=j&&n.test(j.nodeName||\"\")),p=b.firesLoad||\"src\"in a&&(h||g||l),o={target:a},p&&(u(a,T,!0),clearTimeout(m),m=k(T,2500),s(a,d.loadingClass),u(a,Z,!0)),l&&q.call(j.getElementsByTagName(\"source\"),_),h?a.setAttribute(\"srcset\",h):g&&!l&&(N.test(a.nodeName)?$(a,g):a.src=g),f&&(h||l)&&w(a,{src:g})),a._lazyRace&&delete a._lazyRace,t(a,d.lazyClass),z(function(){(!p||a.complete&&a.naturalWidth>1)&&(p?T(o):R--,X(o))},!0)}),ba=function(a){var b,c=M.test(a.nodeName),e=c&&(a[i](d.sizesAttr)||a[i](\"sizes\")),f=\"auto\"==e;(!f&&l||!c||!a[i](\"src\")&&!a.srcset||a.complete||r(a,d.errorClass)||!r(a,d.lazyClass))&&(b=v(a,\"lazyunveilread\").detail,f&&E.updateElem(a,!0,a.offsetWidth),a._lazyRace=!0,R++,aa(a,b,f,e,c))},ca=function(){if(!l){if(f.now()-p<999)return void k(ca,999);var a=C(function(){d.loadMode=3,W()});l=!0,d.loadMode=3,W(),j(\"scroll\",function(){3==d.loadMode&&(d.loadMode=2),a()},!0)}};return{_:function(){p=f.now(),c.elements=b.getElementsByClassName(d.lazyClass),g=b.getElementsByClassName(d.lazyClass+\" \"+d.preloadClass),L=d.hFac,j(\"scroll\",W,!0),j(\"resize\",W,!0),a.MutationObserver?new MutationObserver(W).observe(e,{childList:!0,subtree:!0,attributes:!0}):(e[h](\"DOMNodeInserted\",W,!0),e[h](\"DOMAttrModified\",W,!0),setInterval(W,999)),j(\"hashchange\",W,!0),[\"focus\",\"mouseover\",\"click\",\"load\",\"transitionend\",\"animationend\",\"webkitAnimationEnd\"].forEach(function(a){b[h](a,W,!0)}),/d$|^c/.test(b.readyState)?ca():(j(\"load\",ca),b[h](\"DOMContentLoaded\",W),k(ca,2e4)),c.elements.length?(V(),z._lsFlush()):W()},checkElems:W,unveil:ba}}(),E=function(){var a,c=A(function(a,b,c,d){var e,f,g;if(a._lazysizesWidth=d,d+=\"px\",a.setAttribute(\"sizes\",d),n.test(b.nodeName||\"\"))for(e=b.getElementsByTagName(\"source\"),f=0,g=e.length;g>f;f++)e[f].setAttribute(\"sizes\",d);c.detail.dataAttr||w(a,c.detail)}),e=function(a,b,d){var e,f=a.parentNode;f&&(d=y(a,f,d),e=v(a,\"lazybeforesizes\",{width:d,dataAttr:!!b}),e.defaultPrevented||(d=e.detail.width,d&&d!==a._lazysizesWidth&&c(a,f,e,d)))},f=function(){var b,c=a.length;if(c)for(b=0;c>b;b++)e(a[b])},g=C(f);return{_:function(){a=b.getElementsByClassName(d.autosizesClass),j(\"resize\",g)},checkElems:g,updateElem:e}}(),F=function(){F.i||(F.i=!0,E._(),D._())};return c={cfg:d,autoSizer:E,loader:D,init:F,uP:w,aC:s,rC:t,hC:r,fire:v,gW:y,rAF:z}}});</script><script type=\"text/javascript\">/*! lazysizes - v4.0.1 */!function(a,b){var c=function(){b(a.lazySizes),a.removeEventListener(\"lazyunveilread\",c,!0)};b=b.bind(null,a,a.document),\"object\"==typeof module&&module.exports?b(require(\"lazysizes\")):a.lazySizes?c():a.addEventListener(\"lazyunveilread\",c,!0)}(window,function(a,b,c){\"use strict\";function d(a,c){if(!g[a]){var d=b.createElement(c?\"link\":\"script\"),e=b.getElementsByTagName(\"script\")[0];c?(d.rel=\"stylesheet\",d.href=a):d.src=a,g[a]=!0,g[d.src||d.href]=!0,e.parentNode.insertBefore(d,e)}}var e,f,g={};b.addEventListener&&(f=/\\(|\\)|\\s|\'/,e=function(a,c){var d=b.createElement(\"img\");d.onload=function(){d.onload=null,d.onerror=null,d=null,c()},d.onerror=d.onload,d.src=a,d&&d.complete&&d.onload&&d.onload()},addEventListener(\"lazybeforeunveil\",function(a){if(a.detail.instance==c){var b,g,h,i;a.defaultPrevented||(\"none\"==a.target.preload&&(a.target.preload=\"auto\"),b=a.target.getAttribute(\"data-link\"),b&&d(b,!0),b=a.target.getAttribute(\"data-script\"),b&&d(b),b=a.target.getAttribute(\"data-require\"),b&&(c.cfg.requireJs?c.cfg.requireJs([b]):d(b)),h=a.target.getAttribute(\"data-bg\"),h&&(a.detail.firesLoad=!0,g=function(){a.target.style.backgroundImage=\"url(\"+(f.test(h)?JSON.stringify(h):h)+\")\",a.detail.firesLoad=!1,c.fire(a.target,\"_lazyloaded\",{},!0,!0)},e(h,g)),i=a.target.getAttribute(\"data-poster\"),i&&(a.detail.firesLoad=!0,g=function(){a.target.poster=i,a.detail.firesLoad=!1,c.fire(a.target,\"_lazyloaded\",{},!0,!0)},e(i,g)))}},!1))});</script>')
    print "==== Lazyload applied "
    file = open(indexPath, 'w')
    file.write(html)
file.close( )

if int(deleteOriginHtml):
	os.remove(htmlOriginalPath)
	print "==== Original Html Deleted "


##
## github
##

def executeCommand(cmd,arg=""):
	pr = subprocess.Popen(cmd+arg, cwd = parentDir, shell = True, stdout = subprocess.PIPE, stderr = subprocess.PIPE )
	(out, error) = pr.communicate()
	if str(error):
		print "Error : " + str(error)
	if str(out):
		print "out : " + str(out)

executeCommand("git add .")
print "==== Git stage all "
executeCommand("git commit -m ",commentContent)
print "==== Git commit all "
executeCommand("git push -u origin master")
print "==== All filed uploaded to Github "



"""
git reset --hard HEAD
git clean -f -d
git pull

status = subprocess.call("pngquant.exe " + pngtoModify + args, shell=True)

#os.path.dirname()
pngquant.exe @path --force --verbose --quality=45-80 --ext=.png
print os.path.normpath(refFile)   #输出'/Volumes/Leopard/Users/Caroline/Desktop/1.mp4'
print os.path.getsize(refFile)    #输出文件大小（字节为单位）
print os.path.getctime(refFile)   #输出文件创建时间
print os.path.getatime(refFile)   #输出最近访问时间1318921018.0
print time.gmtime(os.path.getmtime(refFile))   #以struct_time形式输出最近修改时间
print os.path.abspath(moFile)    #输出绝对路径'/Volumes/Leopard/Users/Caroline/Desktop/1.mp4'

repo = Gittle.init(parentDir)
for root, dirs, files in os.walk(parentDir):
	for file in files:
		repo.stage(os.path.join(root, file))
repo.commit(message=commentContent)
repo.push()

# subprocess.call("git init", cwd=parentDir, shell=True)
# subprocess.call("git add .", cwd=parentDir, shell=True)
# subprocess.call("git status", cwd=parentDir, shell=True)
# subprocess.call("git commit -m " + commentContent, cwd=parentDir, shell=True)
# subprocess.call("git push -u origin master", cwd=parentDir, shell=True)
pr = subprocess.Popen( "git log" , cwd = parentDir, shell = True, stdout = subprocess.PIPE, stderr = subprocess.PIPE )
cwd=r'd:\test\local'


pr = subprocess.Popen("git commit -m " + commentContent, cwd = parentDir, shell = True, stdout = subprocess.PIPE, stderr = subprocess.PIPE )
pr = subprocess.Popen( "git add ." , cwd = parentDir, shell = True, stdout = subprocess.PIPE, stderr = subprocess.PIPE )
pr = subprocess.Popen( "git init" , cwd = parentDir, shell = True, stdout = subprocess.PIPE, stderr = subprocess.PIPE )
pr = subprocess.Popen( "git push -u origin master" , cwd = parentDir, shell = True, stdout = subprocess.PIPE, stderr = subprocess.PIPE )

"""
