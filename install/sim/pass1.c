/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: pass1.c,v 2.45 2020-08-14 16:56:52 dick Exp $
*/

#include	<string.h>

#include	"pass1.h"

#include	"debug.par"
#include	"global.h"
#include	"text.h"
#include	"token.h"
#include	"tokenarray.h"
#include	"lang.h"
#include	"options.h"

#ifdef	DB_TEXT
#include	"pass1_db.i"
#endif

static void
do_separator(const struct text *txt, const char *fname) {
	if (!Is_Set_Option('T')) {
		fprintf(Output_File, "File %s: new/old separator\n", fname);
	}
	if (Number_of_New_Texts == Number_of_Texts) {
		/* no new Number_of_New_Texts set yet */
		Number_of_New_Texts = txt - &Text[0];
	} else {
		Fatal("more than one new/old separator");
	}
}

static int
read_text(const char *fname, struct text *txt) {
	int file_opened = 0;
	if (Open_Text(txt)) {
		file_opened = 1;
	} else {
		/* print a warning */
		fprintf(Output_File, "File %s: >>>> cannot open <<<<\n", fname);
		/* the file has still been opened with a null file
		   for uniformity
		*/
	}

	while (Next_Text_Token_Obtained()) {
		if (!Token_EQ(lex_token, End_Of_Line)) {
			Store_In_Token(lex_token);
		}
	}
	Close_Text();
	txt->tx_limit = Token_Array_Length();
	txt->tx_EOL_terminated =
		Token_EQ(lex_token, End_Of_Line);

#ifdef	DB_TEXT
	db_print_text(txt);
#endif	/* DB_TEXT */

	return file_opened;
}

static void
fprint_count(FILE *f, size_t cnt, const char *unit) {
	/*	Prints a grammatically correct string "%u %s[s]"
		for units that form their plural by suffixing -s.
	*/
	fprintf(f, "%s %s%s", size_t2string(cnt), unit, (cnt == 1 ? "" : "s"));
}

static void
report_file(const char *fname, struct text *txt) {
	fprintf(Output_File, "File %s: ", fname);
	fprint_count(Output_File, txt->tx_limit - txt->tx_start, Token_Name);
	fprintf(Output_File, ", ");
	fprint_count(Output_File,
		lex_nl_cnt - 1 + (!txt->tx_EOL_terminated ? 1 : 0), "line"
	);
	if (!txt->tx_EOL_terminated) {
		fprintf(Output_File, " (not NL-terminated)");
	}
	if (lex_non_ASCII_cnt) {
		fprintf(Output_File, ", ");
		fprint_count(Output_File,
			lex_non_ASCII_cnt, "non-ASCII character"
		);
	}
	fprintf(Output_File, "\n");
}

static void
read_file(const char *fname, struct text *txt) {
	txt->tx_fname = fname;
	txt->tx_pos = 0;
	txt->tx_start = Token_Array_Length();
	txt->tx_limit = Token_Array_Length();

	if (Is_New_Old_Separator(fname)) {
		do_separator(txt, fname);
	}
	else {	/* it is a real file */
		int ok = read_text(fname, txt);
		if (ok && !Is_Set_Option('T')) {
			report_file(fname, txt);
		}
	}

	fflush(Output_File);
}

void
Read_Input_Files(int argc, const char *argv[]) {
	int n;

	Init_Text(argc);
	Init_Token_Array();

	/* Initially assume all texts to be new */
	Number_of_New_Texts = Number_of_Texts;

	/* Read the files */
	for (n = 0; n < Number_of_Texts; n++) {
		/* do one argument/file name */
		read_file(argv[n],&Text[n]);
	}

	/* report total */
	int sep_present = (Number_of_Texts != Number_of_New_Texts);
	fprintf(Output_File, "Total input: ");
	fprint_count(Output_File,
		     (!sep_present ? Number_of_Texts : Number_of_Texts - 1),
		     "file"
	);
	fprintf(Output_File, " (%d new, %d old), ",
		Number_of_New_Texts,
		(!sep_present ? 0 :  Number_of_Texts - Number_of_New_Texts - 1)
	);
	fprint_count(Output_File, Token_Array_Length() - 1, Token_Name);
	fprintf(Output_File, "\n\n");
	fflush(Output_File);
}
