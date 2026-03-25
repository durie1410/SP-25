Set fso = CreateObject("Scripting.FileSystemObject")
Set WshShell = CreateObject("WScript.Shell")

logFile = "C:\laragon\www\quanlythuviennn\storage\logs\scheduler.log"
batPath = "C:\laragon\www\quanlythuviennn\run_schedule.bat"

' Run batch hidden, redirect output to log
WshShell.Run "cmd /c ""call " & batPath & " >> """ & logFile & """ 2>&1""", 0, False

Set WshShell = Nothing
Set fso = Nothing
