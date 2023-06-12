/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: options.c,v 1.31 2020-08-14 16:56:51 dick Exp $
*/

#include	"options.a"

#include	<stdlib.h>

#include	"settings.par"
#include	"global.h"

/* Command-line parameters, with defaults */
int Min_Run_Size = DEFAULT_MIN_RUN_SIZE;
int Page_Width = DEFAULT_PAGE_WIDTH;
int Threshold_Percentage = 1;
const char *Output_Name;		/* for redirecting the output */

enum Value_Type {No_Type, Number_Type, String_Type};
struct option {
	char op_char;		/* char as in call */
	char *op_text;		/* explanatory text */
	enum Value_Type op_type;
	void *op_value;
};

static const struct option optlist[] = {
	{'r', "set minimum run size to N", Number_Type, &Min_Run_Size},

	{' ', "output runs as text (default)", No_Type, 0},
	{'d', "output in a diff-like format", No_Type, 0},
	{'n', "suppress the text of the runs", No_Type, 0},
	{'T', "suppress reporting the input files", No_Type, 0},
	{'p', "output similarity in percentages", No_Type, 0},
	{'P', "main contributing file to percentages only", No_Type, 0},
	{'t', "threshold level of percentages",
		Number_Type, &Threshold_Percentage},

	{'e', "compare each file to each file separately", No_Type, 0},

	{' ', "compare a file to files after it only (default)", No_Type, 0},
	{'a', "compare to all files", No_Type, 0},
	{'S', "compare to old files only", No_Type, 0},
	{'s', "do not compare a file to itself", No_Type, 0},

	{' ', "sorted output, most significant first (default)", No_Type, 0},
	{'u', "unbuffered, unsorted output", No_Type, 0},

	{' ', "miscellaneous options:", No_Type, 0},
	{'f', "function-like forms only", No_Type, 0},
	{'F', "keep function identifiers in tact", No_Type, 0},
	{'R', "recurse into subdirectories", No_Type, 0},
	{'i', "read arguments (file names) from standard input", No_Type, 0},
	{'o', "write output to file F", String_Type, &Output_Name},
	{'w', "set page width to N", Number_Type, &Page_Width},
	{'O', "show command line options at start-up", No_Type, 0},
	{'M', "show memory usage info at close-down", No_Type, 0},
	{'h', "show avaibale options and stop", No_Type, 0},
	{'v', "show version and subject and stop", No_Type, 0},
	{'-', "lexical scan output only", No_Type, 0},
	{0, 0, 0, 0}
};

static char options[128];

static void
set_option(char ch) {
	options[(int)ch]++;
}

int
Is_Set_Option(int ch) {
	return options[ch];
}

void
Show_All_Options(void) {
	fprintf(stderr, "Possible options are:\n");
	const struct option *op;
	for (op = optlist; op->op_char; op++) {
		if (op->op_char == ' ') {
			fprintf(stderr, "\n\t\t%s\n", op->op_text);
		} else {
			fprintf(stderr, "\t-%c%c\t%s\n",
				op->op_char,
				(	op->op_type == Number_Type ? 'N' :
					op->op_type == String_Type ? 'F' :
					' '
				),
				op->op_text
			);
		}
	}
}

static void
bad_option_exit(char *msg, int c) {
	fprintf(stderr, "%s: ", Program_Name);
	fprintf(stderr, msg, c);
	fprintf(stderr, "\n");

	Show_All_Options();
	exit(1);
}

static int
opt_value(const struct option *op, const char *arg, const char *argv[]) {
	/* get the string and the number of args consumed */
	const char *string;
	int consumed;
	if (*arg) {
		string = arg, consumed = 1;
	}
	else if (argv[1]) {
		string = argv[1], consumed = 2;
	} else {
		string = 0, consumed = 0;
	}
	if (!string || !*string) {
		bad_option_exit("option -%c requires another argument",
			op->op_char
		);
		/*NOTREACHED*/
	}

	switch (op->op_type) {
	case Number_Type:
		*(int *)op->op_value = atoi(string);
		break;
	case String_Type:
		*(const char **)op->op_value = string;
		break;
	}

	return consumed;
}

static int
do_arg(const struct option *optlist, const char *arg, const char *argv[]) {
	int consumed = 0;

	while (*arg) {
		/* treat argument character */
		char opc = *arg++;
		const struct option *op;

		for (op = optlist; op->op_char; op++) {
			if (opc == op->op_char) {
				set_option(opc);
				if (op->op_type != No_Type) {
					consumed = opt_value(op, arg, argv);
				}
				break;
			}
		}
		if (!op->op_char) {
			bad_option_exit("option -%c unknown", opc);
			/*NOTREACHED*/
		}
		if (consumed) break;
	}
	if (!consumed) {
		consumed = 1;
	}

	return consumed;
}

static void
allow_at_most_one_option_out_of(const char *opts) {
	const char *first;
	for (first = opts; *first; first++) {
		const char *second;
		for (second = first + 1; *second; second++) {
			if (Is_Set_Option(*first) && Is_Set_Option(*second)) {
				char msg[256];
				sprintf(msg,
					"options -%c and -%c are incompatible",
					*first, *second
				);
				Fatal(msg);
			}
		}
	}
}

static void
check_options_compatibility(void) {
	if (Is_Set_Option('p')) {
		set_option('s');
	}

	allow_at_most_one_option_out_of("dnp");	/* alternative output formats */
	allow_at_most_one_option_out_of("aS");	/* alternative ranges */
	allow_at_most_one_option_out_of("sS");	/* self is outside old files */

	if (Is_Set_Option('t')) {
		/* threshold means percentages */
		if (!Is_Set_Option('p'))
		    Fatal("option -t requires -p");
	}
	if (Is_Set_Option('P')) {
		if (!Is_Set_Option('p'))
		    Fatal("option -P requires -p");
	}

	/* Check the value options */
	if (Min_Run_Size <= 0)
		Fatal("bad run size");
	if (Page_Width <= 0)
		Fatal("bad page width");

	if (Is_Set_Option('p')) {
		if ((Threshold_Percentage > 100) || (Threshold_Percentage <= 0))
			Fatal("threshold percentage must be between 1 and 100");
	}
}

int
Get_Options(int argc, const char *argv[]) {
	int skips = 0;

	while (argc > 0 && argv[0][0] == '-') {
		int consumed = do_arg(optlist, &argv[0][1], argv);
		argc -= consumed, argv += consumed, skips += consumed;
	}

	check_options_compatibility();

	return skips;
}

static int
is_essential_option(char op_char) {
	if (op_char == 'r') return 1;
	if (op_char == 'w') return 1;
	if (Is_Set_Option('p') && op_char == 't') return 1;
	return 0;
}

void
Show_Actual_Options(void) {
	const struct option *op;

	fprintf(stdout, "Option settings:");
	fprintf(stdout, "\n");
	for (op = optlist; op->op_char; op++) {
		if (	Is_Set_Option(op->op_char)
		||	is_essential_option(op->op_char)
		) {
			fprintf(stdout, " -%c", op->op_char);
			switch (op->op_type) {
			case No_Type:	break;
			case Number_Type:
				fprintf(stdout, "%d",
					*(int *)op->op_value);
				break;
			case String_Type:
				fprintf(stdout, " %s",
					*(const char **)op->op_value);
				break;
			}
			fprintf(stdout, " (%s)\n", op->op_text);
		}
	}
	fprintf(stdout, "\n");
}
