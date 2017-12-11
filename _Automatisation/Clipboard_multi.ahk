;将本地图片插入Markdown
^+C:: ;ctrl+shift+c
clipboard= ;清空剪贴板
clip =
send, ^c
clipwait
Loop, parse, clipboard, `n, `r
{
obj = %A_LoopField%
If InStr(obj, ".png") or InStr(obj, "jpg")
{
clip = 
(
<p align="center">![](%A_LoopField%)</p>
%clip%
)
}
If obj contains .mp3,.wav,.ogg
{
clip = 
(
<p align="center"><audio controls><source src="%A_LoopField%"></audio></p>
%clip%
)
}
If obj contains .mp4,.mkv
{
clip = 
(
<p align="center"><video width="480" height="270" controls><source src="%A_LoopField%"></video></p>
%clip%
)
}
}
clipboard = %clip%
msgbox,0, 多媒体路径已复制!,%clipboard%,0.5
;Options, Title, Text, Timeout
return




