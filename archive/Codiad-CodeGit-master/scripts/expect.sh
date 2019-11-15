#!/bin/bash
#Author: Andr3as
#Last Edit: 11.09.14
#Purpose: Handler for a git requests with authentification

# Copyright (c) Codiad & Andr3as, distributed
# as-is and without warranty under the MIT License. 
# See http://opensource.org/licenses/MIT for more information.
# This information must remain intact.


command=""
password=0
passphrase=0
path=""
user=0

#Handle inputs
while [ "$1" != "" ]
  do
    if [ "$1" == "-u" ]
      then
        user=$2
        shift
        shift
      elif [ "$1" == "-p" ]
        then
          password=$2
          shift
          shift
      elif [ "$1" == "-k" ]
        then
          passphrase=$2
          shift
          shift
      elif [ "$1" == "-c" ]
        then
          command=$2
          shift
          shift
      elif [ "$1" == "-s" ]
        then
          path=$2
          shift
          shift
      else
        echo "Unknown parameter"
        echo "$1"
        shift
    fi
done

if [ "$command" == "" ]
  then
    echo "No command specified"
    exit 64
fi

if [ "$path" == "" ]
  then
    echo "No path specified"
    exit 64
fi

#Execute command
cd "$path"

/usr/bin/expect <<EOD
    set result 0
    set timeout 180
    spawn -noecho $command
    expect {
        "Username for" {
            if { "$user" == 0 } {
                set result 3
                exit 3
            }
            send "$user\n"
        }
        "Enter passphrase for key" {
            if { "$passphrase" == 0 } {
                set result 7
                exit 7
            }
            send "$passphrase\n"
        }
        "assword" {
            if { "$password" == 0 } {
                set result 4
                exit 4
            }
            send "$password\n"
        }
        "fatal" {
            set result 5
            exit 5
        }
        "error" {
            set result 6
            exit 6
        }
        "eof" {
            exit 0
        }
    }
    expect {
        "Password for" {
            if { "$password" == 0 } {
                set result 4
                exit 4
            }
            send "$password\n"
        }
        "fatal" {
            set result 5
            exit 5
        }
        "error" {
            set result 6
            exit 6
        }
        "eof" {
            exit 0
        }
    }
    expect {
        "fatal" {
            set result 5
            exit 5
        }
        "error" {
            set result 6
            exit 6
        }
        "eof" {
            exit 0
        }
    }
EOD

exit $result