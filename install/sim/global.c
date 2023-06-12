/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: global.c,v 2.3 2020-08-14 16:56:50 dick Exp $
*/

#include	"global.h"

#include	<stdlib.h>
#include	<string.h>

							/* GLOBAL DATA */

const char *Program_Name;
const char *Version;
const char *Subject;

FILE *Output_File;
FILE *Debug_File;

							/* SERVICE ROUTINES */

int
Is_New_Old_Separator(const char *s) {
	if (strcmp(s, "/") == 0) return 1;
	if (strcmp(s, "|") == 0) return 1;
	return 0;
}

void
Fatal(const char *msg) {
	fprintf(stderr, "%s: %s\n", Program_Name, msg);
	exit(1);
}
