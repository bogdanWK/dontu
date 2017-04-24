$computername = $env:computername

$avg = Get-WmiObject win32_processor -computername $computername | Measure-Object -property LoadPercentage -Average | Foreach {$_.Average}
$mem = Get-WmiObject win32_operatingsystem -ComputerName $computername | Foreach {"{0:N2}" -f ((($_.TotalVisibleMemorySize - $_.FreePhysicalMemory)*100)/ $_.TotalVisibleMemorySize)}
$free = Get-WmiObject Win32_Volume -ComputerName $computername -Filter "DriveLetter = 'C:'" | Foreach {"{0:N2}" -f (($_.FreeSpace / $_.Capacity)*100)}

Write-Host ($avg | Format-List | Out-String)
Write-Host ($mem | Format-List | Out-String)
Write-Host ($free | Format-List | Out-String)

$Outputreport = "{ 'win10': { 'name':, $computername, 'cpu': '$avg%', 'mem': '$mem%', 'disk': '$free%' } }"

$Outputreport | out-file C:\vagrant_data\res_win.json
