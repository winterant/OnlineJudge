/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: global.h,v 2.7 2020-08-14 16:56:50 dick Exp $
*/

/* Should be included in all *.c and *.l files because of printf() blocking */

#include	<stdio.h>

extern const char *Program_Name;
extern const char *Version;
extern const char *Subject;

extern FILE *Output_File;
extern FILE *Debug_File;

/* Service routines */
extern int Is_New_Old_Separator(const char *s);
extern void Fatal(const char *msg);

/* All output goes through designated files, so we block printf, etc. */
#undef	printf
#define	printf	use_fprintf
#undef	putchar
#define	putchar	use_fprintf
