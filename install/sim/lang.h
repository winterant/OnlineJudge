/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: lang.h,v 1.15 2020-08-14 16:56:51 dick Exp $
*/

/*
	The interaction with the *lang.l files is handled by two modules:
	    lang.[ch]
	        an abstract module which provides declarations for facilities
		provided by the *lang.l and to be used by the program;
	    lex.[ch]
	        provides auxiliary macros and routines to be used in the
		*lang.l file.
*/

/*
	There is a dummy implementation lang.c for use in the absence of a
	*lang.c .
*/

#include	<stdio.h>
#include	"token.h"

#ifdef	_LANG_A_
/* defined by lex(1) or flex(1) */
extern FILE *yyin;
extern int yylex(void);
extern void yystart(void);
#endif	/*_LANG_A_*/

/* defined by the pertinent *lang.l */
extern Token lex_token;			/* token produced, or End_Of_Line */
extern size_t lex_nl_cnt;		/* line count */
extern size_t lex_tk_cnt;		/* token position */
extern size_t lex_non_ASCII_cnt;	/* # of non-ASCII chars found */

extern void Init_Language(void);
