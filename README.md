# Library Stacks Management System    [![Build Status](https://travis-ci.org/rochester-rcl/rcl-lsms.svg?branch=master)](https://travis-ci.org/rochester-rcl/rcl-lsms)

### Preamble

  This project is an adaptation of the LSMS created by Nackil Suny and Erin Kim of the University of Hawaii at Manoa.

### I. Introduction

  LSMS is a stacks management system broken down into two parts, the scanning
  interface and the session manager.  The scanning interface is paired with a
  laptop/scanner and used in the stacks, scanning books one by one.  The session
  manager is responible for processing all of the backend data and generating
  reports.  The greatest advantage of the system is the immediacy and engagement.

  Initially retrieved from:  http://assist.hawaii.edu/LSMS/download

### II. Requirements

  LSMS uses common open source web application components, including the
  Apache web server, PHP scripting language, and MySQL database.  LSMS also
  relies on the fact that you are a Voyager library running Oracle databases.
  PHP specifics: PHP 5, PECL OCI8 >= 1.1.0.

### III. Installation Procedure

  Unzip the LSMS directory into your webroot folder so it could be accessed here:
  http://library.state.edu/LSMS (or a similar fashion).

  1. Run the INSTALL script

  2. Access the configuration file in shared/CleanConfig.php and fill in the rest of
     the required variables, **then rename to Config.php**

  3. Use your web browser to access the Location Manager at http://<webroot>/LSMS/locationmanager
     to setup the locations you will be using with the system

  4. Complete!
     Begin using the system at http://<webroot>/LSMS
     If you want to access the Session Manager, you may do so here: http://<webroot>/LSMS/sessionmanager

### IV. Copyright and Licensing

  Copyright (C) Univeristy of Hawaii 2012.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 2,
  as published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

### V. Additional Credits

  The book icon provided with LSMS is from Michael Stutz <stutz@dsl.org>
