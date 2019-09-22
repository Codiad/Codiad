#!/usr/bin/env python
#Author: Andr3as
#Year: 2016
#Purpose: Handler for git requests with authentification
#MinPython 2.7

# Copyright (c) Andr3as
# as-is and without warranty under the MIT License. 
# See http://opensource.org/licenses/MIT for more information.
# This information must remain intact.

#http://www.tutorialspoint.com/python/python_command_line_arguments.htm
import os, sys, argparse, pexpect

#Parse arguments
parser = argparse.ArgumentParser(description='Handler for git requests with authentification.')
parser.add_argument('-u', '--user',
            action="store", dest="user",
            help="Username for authentification", default="")
parser.add_argument('-p', '--password',
            action="store", dest="password", 
            help="Password for authentification", default="")
parser.add_argument('-k', '--passphrase',
            action="store", dest="passphrase",
            help="Passphrase for authentification", default="")
parser.add_argument('-c', '--command',
            action="store", dest="command", 
            help="Command to execute", default="")
parser.add_argument('-s', '--path',
            action="store", dest="path", 
            help="Repository location", default="")
parser.add_argument('--debug', dest='debug', action='store_const',
           const=True, default=False,
           help='Either to debug script')
parser.add_argument('--test', dest='test', action='store_const',
           const=True, default=False,
           help='Test python for modules')

arguments = parser.parse_args()

if arguments.debug:
    print(arguments)

if arguments.test:
    exit(0)

#Check arguments
if arguments.command == "":
    print("No command specified")
    sys.exit(64)

if arguments.path == "":
    print("No path specified")
    sys.exit(64)

#Change current path
os.chdir(arguments.path)

#Execute command
timeout = 180
child = pexpect.spawn(arguments.command, timeout=timeout)

index = child.expect(['Username for', 'Enter passphrase for key', 'assword', 'fatal', 'error',
                    pexpect.EOF, pexpect.TIMEOUT])
if index == 0:
    if arguments.user == "":
        sys.exit(3)
    child.sendline(arguments.user)
elif index == 1:
    if arguments.passphrase == "":
        sys.exit(7)
    child.sendline(arguments.passphrase)
elif index == 2:
    if arguments.password == "":
        sys.exit(4)
    child.sendline(arguments.password)
elif index == 3:
    #Fatal
    sys.exit(5)
elif index == 4:
    #Error
    sys.exit(6)
elif index == 5:
    #EOF
    sys.exit(0)
elif index == 6:
    #TIMEOUT
    sys.exit(65)

index = child.expect(['Password for', 'fatal', 'error',
                    pexpect.EOF, pexpect.TIMEOUT])
if index == 0:
    if arguments.password == "":
        sys.exit(4)
    child.sendline(arguments.password)
elif index == 1:
    #Fatal
    sys.exit(5)
elif index == 2:
    #Error
    sys.exit(6)
elif index == 3:
    #EOF
    sys.exit(0)
elif index == 4:
    #TIMEOUT
    sys.exit(65)
    
index = child.expect(['fatal', 'error', pexpect.EOF, pexpect.TIMEOUT])
if index == 0:
    #Fatal
    sys.exit(5)
elif index == 1:
    #Error
    sys.exit(6)
elif index == 2:
    #EOF
    sys.exit(0)
elif index == 3:
    #TIMEOUT
    sys.exit(65)