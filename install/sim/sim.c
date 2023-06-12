/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: sim.c,v 2.84 2020-08-14 16:56:53 dick Exp $
*/

#include	"global.h"
#include	"options.a"
#include	"newargs.h"
#include	"token.h"
#include	"tokenarray.h"
#include	"text.h"
#include	"compare.h"
#include	"pass1.h"
#include	"pass2.h"
#include	"pass3.h"
#include	"percentages.h"
#include	"stream.h"
#include	"lang.h"

#include	"Malloc.h"
#include	"any_int.h"

#ifdef	ARG_TEST
static void
show_args(const char *msg, int argc, const char *argv[]) {
	fprintf(stdout, "%s: ", msg);

	int i;
	for (i = 0; i < argc; i++) {
		fprintf(stdout, "arg[%d] = %s; ", i, argv[i]);
	}
	fprintf(stdout, "\n");
}
#endif	/* ARG_TEST */

int
main(int argc, const char *argv[]) {

	/* The value of Version derives from the string macro VERSION in the
	   Makefile if present. If not, a build time stamp is created.
	*/
	static /* to avoid scope violation */ char version[40];
#ifdef	VERSION
	sprintf(version, "Version %s", VERSION);
#else
	sprintf(version, "Build %s, %s", __DATE__, __TIME__);
#endif
	Version = version;

	/* Save program name */
	Program_Name = argv[0];
	argv++, argc--;				/* and skip it */

	/* Set the output and debug streams */
	Debug_File = stdout;
	Output_File = stdout;

	/* options from command line */
	{	int n_op = Get_Options(argc, argv);
		argc -= n_op, argv += n_op;	/* and skip them */
	}

	/* override from language file */
	Init_Language();

	/* Treat the simple options first */
	if (Is_Set_Option('h')) {
		Show_All_Options();
		return 0;
	}

	if (Is_Set_Option('v')) {
		fprintf(stdout, "Version: %s\n", Version);
		if (Subject) fprintf(stdout, "Subject: %s\n", Subject);
		return 0;
	}

	if (Output_Name) {
		Output_File = fopen(Output_Name, "w");
		if (Output_File == 0) {
			char msg[1000];
			sprintf(msg, "cannot open output file `%s'",
				Output_Name);
			Fatal(msg);
			/*NOTREACHED*/
		}
	}

	/* Treat the input-determining options */
	if (Is_Set_Option('i')) {
		/* read input file names from standard input */
		if (argc != 0)
			Fatal("-i option conflicts with file arguments");
		Get_New_Std_Input_Args(&argc, &argv);
	}
	if (Is_Set_Option('R')) {
		Get_New_Recursive_Args(&argc, &argv);
	}
	/* (argc, argv) now represents new_file* [ / old_file*] */

	/* Optionally show command line options */
	if (Is_Set_Option('O')) {
		Show_Actual_Options();
	}

	/* Here the real work starts */

	if (Is_Set_Option('-')) {
		/* Just the lexical scan */
		while (argv[0]) {
			const char *arg = argv[0];
			if (!Is_New_Old_Separator(arg)) {
				Print_Stream(arg);
			}
			argv++;
		}
	} else {	/* The works */
		Read_Input_Files(argc, argv);	/* turns files into texts */
		Compare_Files();		/* turns texts into runs */
		if (Is_Set_Option('p')) {
			Print_Percentages();
		} else {
			Retrieve_Runs();
			Print_Runs();
		}
	}

	Free_Text();
	Free_Token_Array();
	if (Is_Set_Option('M')) {
		Report_Memory_Status(stderr);
	}

	return 0;
}
