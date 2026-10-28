$ErrorActionPreference = 'Stop'

$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"
$line = '127.0.0.1      naijasquare.dev        #laragon magic!'
$current = Get-Content -Path $hostsPath -Raw
if ($current -notmatch 'naijasquare\.dev') {
    Add-Content -Path $hostsPath -Value "`r`n$line"
    Write-Output 'Added naijasquare.dev to hosts.'
} else {
    Write-Output 'Hosts already contains naijasquare.dev.'
}

certutil -addstore -f ROOT 'C:\laragon\etc\ssl\naijasquare.dev.crt'

$httpd = 'C:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin\httpd.exe'
& $httpd -t
if ($LASTEXITCODE -ne 0) {
    throw 'Apache config test failed.'
}

& $httpd -k restart
Write-Output 'Apache restarted.'
