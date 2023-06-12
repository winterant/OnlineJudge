/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: stream.c,v 2.26 2020-08-14 16:56:53 dick Exp $
*/

#include	"stream.h"

#include	"system.par"
#include	"global.h"
#include	"token.h"
#include	"lang.a"
#include	"fname.h"

static FILE *
fopen_regular_file(const char *fname) {
	struct stat buf;

	if (Stat(str2Fname(fname), &buf) != 0) return 0;
	if ((buf.st_mode & S_IFMT) != S_IFREG) return 0;
	return Fopen(str2Fname(fname), "r");
}

int
Open_Stream(const char *fname) {
	lex_nl_cnt = 1;
	lex_tk_cnt = 0;		/* but is raised before the token is delivered,
				   so effectively *_tk_cnt starts at 1:
				   TK_CNT_HORROR
				*/
	lex_non_ASCII_cnt = 0;

	/* start the lex machine */
	yyin = fopen_regular_file(fname);
	int ok = (yyin != 0);
	if (!ok) {
		/* fake a stream, to simplify the rest of the program */
		yyin = fopen(NULLFILE, "r");
	}
	yystart();
	return ok;
}

int
Next_Stream_Token_Obtained(void) {
	return yylex();
}

void
Close_Stream(void) {
	if (yyin) {
		fclose(yyin);
		yyin = 0;
	}
}

void
Print_Stream(const char *fname) {
	fprintf(Output_File, "File %s:", fname);
	if (!Open_Stream(fname)) {
		fprintf(Output_File, " cannot open\n");
		return;
	}

	fprintf(Output_File, " showing the %s stream\n", Token_Name);

	lex_token = End_Of_Line;
	do {
		if (Token_EQ(lex_token, End_Of_Line)) {
			fprintf(Output_File,
				"line # = %s, %s # = %s:\n",
				size_t2string(lex_nl_cnt),
				Token_Name,
				size_t2string(lex_tk_cnt)
			);
		}
		else {
			extern char *yytext;
			fprintf(Output_File, " %s -> ", yytext);
			fprint_Token(Output_File, lex_token);
			fprintf(Output_File, "\n");
		}
	} while (Next_Stream_Token_Obtained());

	fprintf(Output_File, "\n");

	Close_Stream();
}
